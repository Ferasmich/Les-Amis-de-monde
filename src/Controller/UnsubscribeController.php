<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Entity\Unsubscriber;
use App\Form\UnsubscribeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UnsubscribeController extends AbstractController
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route('/unsubscribe/{token}', name: 'app_unsubscribe')]
    public function unsubscribe(Request $request, EntityManagerInterface $entityManager, string $token): Response
    {
        // Retrieve subscriber by token
        $subscriber = $entityManager->getRepository(Subscriber::class)->findOneBy(['unsubscribeToken' => $token]);

        // Check if subscriber is found
        if (!$subscriber) {
            // Invalid unsubscribe token message
            return $this->render('newsletter/message.html.twig', [
                'messageType' => 'error',
                'message' => 'Invalid unsubscribe token. Please check your email for a valid unsubscribe link.',
                'autoRedirect' => true,
            ]);
        }

        // Create a new Unsubscriber entity
        $unsubscriber = new Unsubscriber();
        $unsubscriber->setEmail($subscriber->getEmail());
        $unsubscriber->setFirstName($subscriber->getFirstName());
        $unsubscriber->setLastName($subscriber->getLastName());
        $unsubscriber->setSubscribedAt($subscriber->getSubscribedAt());

        // Create the form using the Unsubscriber entity
        $form = $this->createForm(UnsubscribeType::class, $unsubscriber);
        $form->handleRequest($request);

        // Handle form submission
        if ($form->isSubmitted() && $form->isValid()) {

            $existingSubscriber = $entityManager->getRepository(Unsubscriber::class)->findOneBy(['email' => $subscriber->getEmail()]);
    
            if ($existingSubscriber) {
                // Remove subscriber from Subscriber table
                $entityManager->remove($existingSubscriber);
                $entityManager->flush();
            }

            // Set unsubscribedAt and unsubscribeReason
            $unsubscriber->setUnsubscribedAt(new \DateTime());
            $unsubscriber->setUnsubscribeReason($form->get('unsubscribeReason')->getData());

            // Persist and flush the Unsubscriber entity
            $entityManager->persist($unsubscriber);
            $entityManager->flush();

            // Remove subscriber from Subscriber table
            $entityManager->remove($subscriber);
            $entityManager->flush();

            // Call the sendSubscriptionEmail method passing $this->mailer as argument
            $this->sendSubscriptionEmail($subscriber, $this->mailer);

            // Unsubscribe success message
            return $this->render('newsletter/message.html.twig', [
                'messageType' => 'success',
                'message' => 'You are successfully unsubscribed from our newsletter!',
                'autoRedirect' => true,
                
            ]);
        }

        // Render the template with the form
        return $this->render('newsletter/unsubscribe.html.twig', [
            'form' => $form->createView(),
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
            ->htmlTemplate('newsletter/unsub_confirm.html.twig') // Set the HTML template for the email body
            ->context([
                'subscriber' => $subscriber,
            ]);

        //$mailer->send($email); // Send the email using Symfony's Mailer component

    }
}
