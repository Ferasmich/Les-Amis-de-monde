<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Subscriber;
use App\Form\SubscriberType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SubscriberController extends AbstractController
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route('/subscribe', name: 'app_subscribe')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subscriber = new Subscriber(); // Create a new Subscriber entity

        $form = $this->createForm(SubscriberType::class, $subscriber);

        $form->handleRequest($request);

        // Check if the subscriber's email is already subscribed and confirmed
        $existingSubscriber = $entityManager->getRepository(Subscriber::class)->findOneBy(['email' => $subscriber->getEmail(), 'isConfirmed' => true]);
        if ($existingSubscriber) {
            $this->addFlash('warning', 'You are already subscribed to our newsletter.');
            return $this->redirectToRoute('app_subscribe');
        }

        // Check if the subscriber's email is already subscribed but not confirmed
        $existingUnconfirmedSubscriber = $entityManager->getRepository(Subscriber::class)->findOneBy(['email' => $subscriber->getEmail(), 'isConfirmed' => false]);
        if ($existingUnconfirmedSubscriber) {
            $this->addFlash('info', 'You are already subscribed, Please Confirm the email address.');
            $this->sendSubscriptionEmail($existingUnconfirmedSubscriber, $this->mailer);
            return $this->redirectToRoute('app_subscribe');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $subscriber->setSubscribedAt(new \DateTime()); // Set subscribedAt to current date/time
            $subscriber->generateUnsubscribeToken();
            $subscriber->generateConfirmationToken(); // Generate unique token

            $entityManager->persist($subscriber);
            $entityManager->flush();

            // Call the sendSubscriptionEmail method passing $this->mailer as argument
            $this->sendSubscriptionEmail($subscriber, $this->mailer);

            $this->addFlash('success', 'We have sent the confirmation email, please Confirm it.');

            return $this->redirectToRoute('app_subscribe'); // Redirect after saving
        }

        return $this->render('newsletter/subscribe.html.twig', [
            'form' => $form->createView(), // Pass the form view to the template
        ]);
    }

    // Function to send subscription email
    private function sendSubscriptionEmail(Subscriber $subscriber, MailerInterface $mailer)
    {
        // Customize this part to match your email content
        $email = (new TemplatedEmail())
            ->from('gestiontms8@gmail.com') // Set the sender email address
            ->to($subscriber->getEmail()) // Set the recipient email address
            ->subject('Subscription Confirmation') // Set the email subject
            ->htmlTemplate('newsletter/confirm.html.twig') // Set the HTML template for the email body
            ->context([
                'subscriber' => $subscriber,
                'confirmationUrl' => $this->generateUrl('app_confirm', ['token' => $subscriber->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL)
            ]);

        //$mailer->send($email); // Send the email using Symfony's Mailer component
    }
}
