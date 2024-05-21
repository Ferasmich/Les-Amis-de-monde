<?php

namespace App\Controller;

use App\Entity\Donation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email; // Use Email instead of TemplatedEmail

class DonController extends AbstractController
{
    #[Route('/don', name: 'app_don')]
    public function index(): Response
    {
        return $this->render('don/index.html.twig');
    }

    #[Route('/don/save', name: 'app_don_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $civilite = $request->request->get('civiliteDonMateriel');
        $nom = $request->request->get('nomDonMateriel');
        $prenom = $request->request->get('prenomDonMateriel');
        $email = $request->request->get('emailDonMateriel');

        $donation = new Donation();
        $donation->setCivilite($civilite);
        $donation->setNom($nom);
        $donation->setPrenom($prenom);
        $donation->setEmail($email);

        try {
            $entityManager->persist($donation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre don a été enregistré avec succès. nous vous contacterons dès que possible.');

            // Send email notification to manager upon successful save
            $this->sendDonationEmail($donation, $mailer);
        } catch (\Exception $e) {
            // Handle database connection error here
            $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de votre don. Veuillez réessayer plus tard.');
        }

        return $this->redirectToRoute('app_don');
    }

    private function sendDonationEmail(Donation $donation, MailerInterface $mailer)
    {
        $email = (new Email())
            ->from($donation->getEmail()) // Consider email filtering
            ->to('gestiontms8@gmail.com')
            ->subject('Nouvelle donation - ' . $donation->getNom() . ' ' . $donation->getPrenom())
            ->text('Une nouvelle donation a été enregistrée. Détails : ' . "\n" .
                '- Nom : ' . $donation->getCivilite() . ' ' . $donation->getNom() . ' ' . $donation->getPrenom() . "\n" .
                '- Email : ' . $donation->getEmail());

        //$mailer->send($email);
    }
}
