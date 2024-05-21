<?php

namespace App\Controller;

use App\Entity\Action;
use App\Form\ActionType;
use App\Repository\ActionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActionController extends AbstractController
{   
    #[Route('/action', name: 'index_action')]
    public function index(ActionRepository $actionRepository): Response
    {
        $actions = $actionRepository->findAll();
        return $this->render('action/index.html.twig', [
            'actions' => $actions,
        ]);
    }

    #[Route('/action/tab', name: 'tab_action')]
    public function tabBord(ActionRepository $actionRepository): Response
    {
        $actions = $actionRepository->findAll();
        return $this->render('action/tabBord.html.twig', [
            'actions' => $actions,
        ]);
    }

    # CREATE
    #[Route("/new", name:"new_action")]
    public function new_action(Request $request , EntityManagerInterface $em){ 
        $action= new Action(); 
        $form = $this->createForm(actionType::class , $action); 
        $data["form"]= $form ; 
        $form->handleRequest($request); 

        if($form->isSubmitted()&& $form->isValid()){ 
        
            $em->persist($action); 
            $em->flush();
            return $this->redirectToRoute("app_user_index");
        }
        return $this->render("action/new_action.html.twig" , $data);

    
    }

 
    #UPDATE
    #[Route("/update/{id}", name:"update_action")]
    public function update( int $id , ActionRepository $actionRepository, Request $request, EntityManagerInterface $em){
        $action = $actionRepository->findOneBy(["id"=> $id]);
        
        $form = $this->createForm(ActionType::class , $action , ["label"=> "modifier"]);

        $form->handleRequest($request); 

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($action); 
            $em->flush(); 
            return $this->redirectToRoute("app_user_index");
        }

        return $this->render("action/update.html.twig", [
            "action" => $action,
            "form" => $form,
        ]);
    }

    #DELETE

    #[Route("/delete/{id}", name: "delete_action")]
    public function delete(EntityManagerInterface $em, ActionRepository $actionRepository, int $id)
    {
        $action = $actionRepository->FindOneBy(["id" => $id]);
        $em->remove($action);
        $em->flush();
         return $this->redirectToRoute("app_user_index");
    }


}
