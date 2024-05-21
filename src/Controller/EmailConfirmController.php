<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Subscriber;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailConfirmController extends AbstractController
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route('/confirm/{token}', name: 'app_confirm')]
    public function confirmEmail(Request $request, EntityManagerInterface $entityManager, string $token): Response
    {
        $subscriber = $entityManager->getRepository(Subscriber::class)->findOneBy(['confirmationToken' => $token]);

        if (!$subscriber) {
            // Redirect to a page indicating successful confirmation
            return $this->render('newsletter/message.html.twig', [
                'messageType' => 'error',
                'message' => 'Invalid unsubscribe token. Please check your email for a valid confirmation link.',
                'autoRedirect' => true,                
            ]);
        }

        // Check if the subscriber is already confirmed
        if ($subscriber->getIsConfirmed()) {
            // Redirect to a page indicating the email is already confirmed
            return $this->render('newsletter/message.html.twig', [
                'messageType' => 'info',
                'message' => 'Your email is already confirmed.',
                'autoRedirect' => true,                
            ]);
        }

        $subscriber->setIsConfirmed(true); // Set is_confirmed to true
        $entityManager->flush();

        // Call the sendSubscriptionEmail method passing $this->mailer as argument
        $this->sendFinalConfirmationEmail($subscriber, $this->mailer);

        // Redirect to a page indicating successful confirmation
            return $this->render('newsletter/message.html.twig', [
                'messageType' => 'success',
                'message' => 'Your email is successfully confirmated to our newsletter!',
                'autoRedirect' => true,                
            ]);
    }

    // Function to send final confirmation email
    private function sendFinalConfirmationEmail(Subscriber $subscriber, MailerInterface $mailer)
    {
        // Customize this part to match your email content
        $email = (new TemplatedEmail())
            ->from('gestiontms8@gmail.com') // Set the sender email address
            ->to($subscriber->getEmail()) // Set the recipient email address
            ->subject('Subscription Confirmation') // Set the email subject
            ->htmlTemplate('newsletter/sub_confirm.html.twig') // Set the HTML template for the email body
            ->context([
                'subscriber' => $subscriber,
                'unsubscribeUrl' => $this->generateUrl('app_unsubscribe', ['token' => $subscriber->getUnsubscribeToken()], UrlGeneratorInterface::ABSOLUTE_URL)
            ]);

        //$mailer->send($email); // Send the email using Symfony's Mailer component

    }
}
