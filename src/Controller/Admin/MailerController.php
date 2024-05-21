<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Entity\MailData;
use App\Repository\SubscriberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Form\MailerType;
use Doctrine\ORM\EntityManagerInterface;

class MailerController extends AbstractController
{
    private $mailer;
    private $router;
    private $userRepository;
    private $subscriberRepository;
    private $twig;
    private $validator;
    private EntityManagerInterface $entityManager;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $router, UserRepository $userRepository, SubscriberRepository $subscriberRepository, \Twig\Environment $twig, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->subscriberRepository = $subscriberRepository;
        $this->twig = $twig;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
    }

    #[Route('/mailer', name: 'app_mailer')]
    public function sendEmailAction(Request $request, FormFactoryInterface $factory)
    {
        $form = $factory->create(MailerType::class);
        $sendTo = 'all';
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $sendTo = $form->get('sendTo')->getData();
            $template = $form->get('template')->getData(); // Get selected template
    
            // Check if the selected template exists
            $templatePath = 'mailer/' . $template . '.html.twig';
            if (!$this->twig->getLoader()->exists($templatePath)) {
                $this->addFlash('error', 'Selected template is not available.');
                return $this->redirectToRoute('app_mailer');
            }
    
            // Validate recipient group
            $errors = $this->validator->validate($sendTo, new \Symfony\Component\Validator\Constraints\Choice([
                'choices' => ['all', 'unpaid', 'subscriber'],
                'message' => 'Invalid recipient group selected.'
            ]));
    
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->redirectToRoute('app_mailer');
            }
    
            $recipients = $this->getRecipients($sendTo);
    
            if (empty($recipients)) {
                $this->addFlash('error', 'No recipients found Please verify your database.');
                return $this->redirectToRoute('app_mailer');
            }
    
            foreach ($recipients as $recipient) {

                // Get unsubscribe token for the recipient
                $unsubscribeToken = $recipient->getUnsubscribeToken();

                // Generate unsubscribe link only if unsubscribe token exists
                if ($unsubscribeToken) {
                    $unsubscribeLink = $this->router->generate('app_unsubscribe', ['token' => $unsubscribeToken], UrlGeneratorInterface::ABSOLUTE_URL);
                } else {
                    $unsubscribeLink = 'newsletter/message.html.twig'; // or any default value you want
                }

                $emailContent = $this->twig->render($templatePath, [
                    'recipient' => $recipient,
                    'subject' => $form->get('subject')->getData(),
                    'body' => $form->get('body')->getData(),
                    'unsubscribe_link' => $unsubscribeLink, // Pass unsubscribe link to template
                ]);
    
                $message = (new Email())
                    ->from(new Address('gestiontms8@gmail.com', 'Les Amis du Monde'))
                    ->to(new Address($recipient->getEmail()))
                    ->subject($form->get('subject')->getData())
                    ->html($emailContent);
    
                $message = $this->handleAttachment($message, $form);
    
                // Save data to the database.
                $mailData = new MailData();
                $mailData->setChoice($sendTo);
                $mailData->setSubject($form->get('subject')->getData());
                $mailData->setBody($form->get('body')->getData());
    
                $this->entityManager->persist($mailData);
                $this->entityManager->flush();
    
                try {
                    #$this->mailer->send($message);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'An error occurred while sending email to ' . $recipient->getEmail() . ': ' . $e->getMessage());
                }
            }
    
            $this->addFlash('success', 'Emails sent successfully to all selected recipients!');
            return $this->redirectToRoute('app_mailer');
        }
    
        return $this->render('mailer/mailer.html.twig', [
            'form' => $form->createView(),
            'sendTo' => $sendTo,
        ]);
    }    

    private function getRecipients($sendTo)
    {
        if ($sendTo === 'all') {
            return $this->userRepository->findAll();
        } elseif ($sendTo === 'unpaid') {
            return $this->userRepository->findBy(['status' => false]);
        } else {
            return $this->subscriberRepository->findBy(['isConfirmed' => true]);
        }
    }

    private function handleAttachment(Email $message, $form): Email
    {
        $attachment = $form->get('attachment')->getData();

        if ($attachment instanceof UploadedFile) {
            try {
                $message->attachFromPath($attachment->getPathname(), $attachment->getClientOriginalName(), $attachment->getMimeType());
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while attaching the file: ' . $e->getMessage());
            }
        }

        return $message;
    }
}