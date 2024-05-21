<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface; // Inject EntityManager
use App\Entity\Contact; // Import the Contact entity
use App\Form\ContactType; // Import the ContactType form
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
class ContactController extends AbstractController
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $Contact = new Contact(); // Create a new Contact entity

        $form = $this->createForm(ContactType::class, $Contact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($Contact); // Persist the Contact entity
            $entityManager->flush(); // Save the Contact to the database

            $this->sendEmailNotification($Contact, $this->mailer); // Send email notification

            $this->addFlash('success', 'You are successfully send your message!');
            return $this->redirectToRoute('app_contact'); // Redirect after saving
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(), // Pass the form view to the template
        ]);
    }

    private function sendEmailNotification(Contact $contact, MailerInterface $mailer)
    {
        $email = (new Email())
            ->from($contact->getEmail())  # Replace with your sender email
            ->to('gestiontms8@gmail.com')  # Replace with admin recipient email
            ->subject('New Contact Form Submission')
            ->text('You have a new contact form submission from: ' . $contact->getNom() . ' ' . $contact->getPrenom() . "\n" .
                'Email: ' . $contact->getEmail() . "\n" .
                'Subject: ' . $contact->getSujet() . "\n" .
                'Message: ' . $contact->getMessage());

            //$mailer->send($email);
    }
}
