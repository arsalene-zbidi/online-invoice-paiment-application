<?php

namespace App\Controller;

use App\Entity\CarteBanquaire;
use App\Entity\CompteBancaire;
use App\Entity\Facture;
use App\Entity\Facturier;
use App\Entity\Operation;
use App\Entity\User;
use App\Services\MailerService;
use App\Services\PdfServices;


use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use http\Message;
use MercurySeries\FlashyBundle\FlashyNotifier;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, EmailType, RadioType, SubmitType, TextType,DateType};


class FactureController extends AbstractController
{
    private Security $security;


    public function __construct(Security $security,)
    {
        $this->security = $security;



    }

    #[Route('/RechercheFacture', name: 'RechercheFacture')]
    public function RechercheFacture(ManagerRegistry $doctrine, Request $request): Response
    {
        $facturier = new Facturier();
        $defaultData = ['message' => 'Facture a rechercher'];
        $form = $this->createFormBuilder($defaultData)
            ->add('Facturier', EntityType::class, [
                'class' => Facturier::class,
                'placeholder' => "Merci de choisir votre facturier",])
            ->add('Ref', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message'=>"Le champs ne doit pas étre vide"]),

                ]

            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            $data = $form->getData();

            $facturier = $data["Facturier"];
            $facture=new Facture();
            if($data["Ref"]==null){
                $facture->setRef("0");
            }else{
                $facture->setRef($data["Ref"]);}
            $facture->setFacturier($data["Facturier"]);

            $repository = $doctrine->getRepository(Facturier::class);
            $fac=$repository->findOneBy(["id"=>$facturier->getId()]);

            return $this->redirectToRoute('listfacture', array('id' => $fac->getId(), 'ref' => $facture->getRef()));


        }


        return $this->render('facture/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/ListFacture/{id}/{ref}', name: 'listfacture')]
    public function Listfacture(ManagerRegistry $doctrine, int $id, string $ref, Request $request): Response
    {   $list[]=0;
        $user = $this->getUser();
        $repository = $doctrine->getRepository(Facturier::class);
        $fac1 = $repository->findOneBy(["id" => $id]);
        $repository1 = $doctrine->getRepository(Facture::class);

        if ($ref == "0" ) {
            if($fac1->getNom()=="STEG" && is_null($user->getCodeSTEG())){
                $this->addFlash("error","merci d'introduire le code STEG dans le Profile");
             return $this->render("facture/ListVide.html.twig");
            }else{$list = $repository1->findFactureByUser($fac1, $user);}

            if($fac1->getNom()=="SONEDE" && is_null($user->getCodeSONEDE())){
                $this->addFlash("error","merci d'introduire le code SONEDE dans le Profile");
                return $this->render("facture/ListVide.html.twig");

            }else{$list = $repository1->findFactureByUser($fac1, $user);}




        } else {


            $list[0] = $repository1->findOneBy(['Ref'=>$ref]);
            dump($list);


        }
        return $this->render('facture/ListFacture.html.twig', [
        'list' => $list
    ]);
    }


    #[Route('/supprimerfacture/{id}/{idfact}', name: 'supprimerfacture')]
    public function SupprimerFacture($id,$idfact, Request $request, ManagerRegistry $doctrine)
    {   $entityManager = $doctrine->getManager();
        $repository = $doctrine->getRepository(Facture::class);
        $fac1=$repository->findOneBy(["id"=>$id]);
        $entityManager->remove($fac1);
        $entityManager->flush();



        return $this->redirectToRoute('listfacture',  array('id' => $idfact,'Ref'=>'null'));
    }
    #[Route('/choix', name: 'choix')]
    public function choix(Request $request,ManagerRegistry $doctrine)
    {
       $choix = $request->get('choix');
       $Ref=$request->get('Ref');
        $repository = $doctrine->getRepository(Facture::class);
        $fac1=$repository->findOneBy(["Ref"=>$Ref]);


       $resp= $this->redirectToRoute('paiment',  array('Ref' => $fac1->getRef(),'choix'=>$choix));

        return new Response($resp);
    }
    #[Route('/paiment/{Ref}/{choix}', name: 'paiment')]
    public function paiment(ManagerRegistry $doctrine,string $Ref,string $choix)
    {  if ($choix =="carte"){

       return $this->redirectToRoute('paimentCarte',  array('Ref' => $Ref));
    }else{
        return $this->redirectToRoute('paimentVirement',  array('Ref' => $Ref));
    }





    }


    #[Route('/paimentCarte/{Ref}', name: 'paimentCarte')]
    public function paimentCarte(ManagerRegistry $doctrine,string $Ref,Request $request,MailerInterface $mailer)
    {  $repository = $doctrine->getRepository(Facture::class);
        $fac1=$repository->findOneBy(["Ref"=>$Ref]);
       $user=$this->getUser();
       $manager=$doctrine->getManager();
        $defaultData = ['message' => 'Paiment Carte'];
        $form = $this->createFormBuilder($defaultData)
            ->add('Email', EmailType::class,[

            ])
            ->add('NumeroCarte', TextType::class, [
                'constraints' => [
                    new NotBlank(['message'=>"Le champs ne doit pas étre vide"]),
                    new Length(['min' => 16,'minMessage'=>'ce champ doit avoire exactement 16 nombre','max'=>16,'maxMessage'=>'ce champ doit avoire exactement 16 nombre']),
            ]])
            ->add('Date_De_Carte', DateType::class,
                [
                    'widget' => 'single_text',

                    // prevents rendering it as type="date", to avoid HTML5 date pickers


                    // adds a class that can be selected in JavaScript
                    'attr' => ['class' => 'js-datepicker'],
                ]

                )
            ->add('Cvv2', TextType::class, [
                'constraints'=>[   new Length(['min'=>3,'max'=>3]),]

            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $user =$this->getUser();
            $compte = new CompteBancaire();
            $repository = $doctrine->getRepository(CompteBancaire::class);
            $compte=$repository->findOneBy(["user"=>$user]);
            $repository = $doctrine->getRepository(CarteBanquaire::class);
            $carte=$repository->findBy(["CompteBanquaire"=>$compte]);
            $cvok=false;
            $carteok=false;
            $dateok=false;

            foreach ($carte as $c) {


                if ($c->getNumCompte() == $data["NumeroCarte"]) {
                     $carteok=true;
                     if ($c->getDate()==$data["Date_De_Carte"]){
                         $dateok=true;
                    if ($c->getCvv2() == $data["Cvv2"]) {
                        $cvok=true;
                        if ($compte->getSolde() >= $fac1->getMontant()) {
                            $novSolde = $compte->getSolde() - $fac1->getMontant();
                            $compte->setSolde($novSolde);
                            $manager->persist($compte);
                            $fac1->setEtat("0");
                            $manager->persist($fac1);
                            //operation:
                            $operation = new Operation();
                            $operation->setMontant($fac1->getMontant());
                            $time = new \DateTime();
                            $operation->setDate($time);
                            $operation->setMotif("réglement facture");
                            $operation->setRibBen($fac1->getFacturier()->getRib());
                            $operation->setRibDon($compte->getRib());
                            $manager->persist($operation);

                            $manager->flush();
                            $message1 = "Paiement a éter fait avec succès";
                            $contant = "<h2 style='color: #1c7430'>Votre paiement a été effectué avec succès</h2><br><h4>Bonjour :</h4>" . $this->getUser()->getprenom() . ' ' . $this->getUser()->getnom() . "<h2 style='color: red'>Voici les information du facture:<h2>"
                                . "<h4> Facturier:</h4>" . $fac1->getFacturier()->getNom() . "<h4> numero de réferance:</h4>" . $Ref . "<h4> Montant:</h4>" . $fac1->getMontant() . 'Dt' . "<h4> Date de paiment:</h4>" . $time->format('Y-m-d H:i:s');
                            $email = (new Email())
                                ->from('bnatest0@gmail.com')
                                ->to($data['Email'])
                                ->subject('Paiment de facture avec MyBna24 Facture ')
                                ->text($message1)
                                ->html($contant);


                            $mailer->send($email);
                            $this->addFlash("success","Paiment à été effectuer avec success");

                        } else {
                            $this->addFlash("error","votre solde est insuffisant");

                        }
                    }
                }}
            }
            if($carteok==false){
                $this->addFlash("error","Le code de carte est incorrect");
            }
            elseif ($dateok==false){
                $this->addFlash("error","Le date de carte est incorrect");
            }
            elseif ($cvok==false){
                $this->addFlash("error","Le code CVV2");
            }

            return $this->redirectToRoute('listfacture', array('id' => $fac1->getFacturier()->getId(),'ref'=>$Ref));
        }



            return $this->renderForm("facture/paimentCarte.html.twig",[
                'facture'=>$fac1,
                'user'=>$user,
                'form' => $form
            ]);




    }
    #[Route('/paimentVirement/{Ref}', name: 'paimentVirement')]
    public function paimentVirement(ManagerRegistry $doctrine,string $Ref,Request $request,MailerService $mailer, MailerInterface $mailer1 ){
        $repository = $doctrine->getRepository(Facture::class);
        $fac1=$repository->findOneBy(["Ref"=>$Ref]);
        $user=$this->getUser();
        $manager=$doctrine->getManager();
        $defaultData = ['message' => 'Paiment virement'];
        $form = $this->createFormBuilder($defaultData, [
            'validation_groups' => "v",


        ])
            ->add('Cin',TextType ::class,[
                'constraints' => [
                    new NotBlank(['groups' => ['verify', 'default']]),
                    new Length(['min' => 8,'minMessage'=>'ce champ doit avoire exactement 16 nombre','max'=>8,'maxMessage'=>'ce champ doit avoire exactement 16 nombre','groups' => ['verify', 'default']]),
                ]
            ])
            ->add('Rib', TextType::class, [
                'constraints' => [
                    new NotBlank(['message'=>"Le champs ne doit pas étre vide",'groups' => ['verify', 'default']]),
                    new Length(['min' => 20,'minMessage'=>'ce champ doit avoire exactement 16 nombre','max'=>20,'maxMessage'=>'ce champ doit avoire exactement 16 nombre','groups' => ['verify', 'default']]),
                ]])
            ->add('Captcha',ReCaptchaType::class,[

            ])
            ->add('verif',SubmitType::class, [
                'validation_groups' => 'verify',
                'attr' => ['class' => 'btn btn-outline-info'],
            ])
            ->add('Payer',SubmitType::class, [
                'validation_groups' => 'default',
                'attr' => ['class' => 'btn btn-outline-success'],
            ])

            ->add('Code', TextType::class, [

                'constraints'=>[   new Length(['min'=>4,'max'=>4,'groups' => [ 'default']]),
                    new NotBlank(['message'=>"Le champs ne doit pas étre vide",'groups' => [ 'default']]),]

            ])
            ->getForm();
            $form->handleRequest($request);
            $session=$request->getSession();
            if($session->has("random")){
                $r=$session->get('random');


            }
            else{
                $session->set('random',random_int(1000,9999 ));
                $r=$session->get('random');

            }
        if ($form->getClickedButton() === $form->get('verif')&& $form->isValid()){

            $data = $form->getData();

            $user =$this->getUser();
            $email=$user->getEmail();
            $session=$request->getSession();
            $r=$session->get("random");
            dump($r);


            $contant="<h1 style='color: #1c7430'>voici le code de verification:</h1>".'code: '.$r;

            $mailer->sendEmail($email,$contant);

        }

        if( $form->getClickedButton() === $form->get('Payer')&& $form->isValid()) {
            $repository = $doctrine->getRepository(CompteBancaire::class);
            $compte=$repository->findOneBy(["user"=>$user]);
            $r=$session->get("random");
            $data = $form->getData();
            $user =$this->getUser();
            if ($data['Cin']==$user->getCin()){
                if ($data['Rib']==$compte->getRib()){
                    if ($data['Code']==$r){
                        $novSolde=$compte->getSolde() - $fac1->getMontant();
                        $compte->setSolde($novSolde);
                        $manager->persist($compte);
                        $fac1->setEtat("0");
                        $manager->persist($fac1);
                        //operation:
                        $operation = new Operation();
                        $operation->setMontant($fac1->getMontant());
                        $time = new \DateTime();
                        $operation->setDate($time);
                        $operation->setMotif("réglement facture");
                        $operation->setRibBen($fac1->getFacturier()->getRib());
                        $operation->setRibDon($compte->getRib());
                        $manager->persist($operation);

                        $manager->flush();
                        $message1="Paiement a éter fait avec succès";
                        $this->addFlash("success",$message1);
                        $contant="<h2 style='color: #1c7430'>Votre paiement a été effectué avec succès</h2><br><h4>Bonjour :</h4>".$this->getUser()->getprenom().' '.$this->getUser()->getnom()."<h2 style='color: red'>Voici les information du facture:<h2>"
                            ."<h4> Facturier:</h4>".$fac1->getFacturier()->getNom()."<h4> numero de réferance:</h4>".$Ref."<h4> Montant:</h4>".$fac1->getMontant().'Dt'."<h4> Date de paiment:</h4>".$time->format('Y-m-d H:i:s');
                        $email = (new Email())
                            ->from('bnatest0@gmail.com')
                            ->to($user->getEmail())

                            ->subject('Paiment de facture avec MyBna24 Facture ')
                            ->text($message1)
                            ->html($contant);


                        $mailer1->send($email);


                        $session->remove('random');
                        $session->clear();
                        return $this->redirectToRoute('listfacture', array('id' => $fac1->getFacturier()->getId(),'ref'=>$Ref));
                    }else{
                        $this->addFlash('error',"Le code est uncorrect");
                        $this->redirectToRoute("paimentVirement", array("Ref"=>$Ref));

                    }



                }else{
                    $this->addFlash("error","Le Rib est uncorrect");
                    $this->redirectToRoute("paimentVirement", array("Ref"=>$Ref));

                }

            }else{
                $this->addFlash("error","Le cin est uncorrect");
                $this->redirectToRoute("paimentVirement", array("Ref"=>$Ref));
            }
        }



        return $this->render("facture/paimentVirement.html.twig",[
            'form'=>$form->createView(),
            'user'=>$user,
            'ref'=>$Ref
        ]);
    }

    #[Route('/details/{Ref}', name: 'details.pdf')]
    public function details(string $Ref,PdfServices $pdf,ManagerRegistry $doctrine){
        $repository = $doctrine->getRepository(Facture::class);
        $fac1=$repository->findOneBy(["Ref"=>$Ref]);
        $time = new \DateTime();
        $date=$time->format('Y-m-d H:i:s');

        $html= $this->render("facture/pdffacture.html.twig",['fac'=>$fac1, 'date'=>$date]);
        ob_get_clean();
        $pdf->showpdfFile($html);

    }
    #[Route('/profile', name: 'profile')]
    public  function profile(ManagerRegistry $doctrine  ,Request $request)
    {
       $user=$this ->getUser();

       if (is_null($user->getCodeSTEG())&& is_null($user->getCodeSONEDE())){

           $defaultData = ['message' => 'add profile'];
           $form = $this->createFormBuilder($defaultData)
               ->add('CodeSTEG',TextType ::class,[
                   'constraints' => [
                       new NotBlank(['message'=>"Le champs ne doit pas étre vide"]),
                       new Length(['min' => 12,'minMessage'=>'ce champ doit avoire exactement 16 nombre','max'=>12,'maxMessage'=>'ce champ doit avoire exactement 16 nombre']),
                   ]
               ])
               ->add('CodeSONEDE', TextType::class, [
                   'constraints' => [
                       new NotBlank(['message'=>"Le champs ne doit pas étre vide"]),
                       new Length(['min' => 12,'minMessage'=>'ce champ doit avoire exactement 16 nombre','max'=>12,'maxMessage'=>'ce champ doit avoire exactement 16 nombre']),
                   ]])


               ->add('Ajouter',SubmitType::class, [
                   'attr' => ['class' => 'btn btn-outline-success'],
               ]) ->getForm();
           $form->handleRequest($request);
           if ($form->isSubmitted() && $form->isValid()) {
               $manager=$doctrine->getManager();
               $data = $form->getData();
               $user->setCodeSTEG($data['CodeSTEG']);
               $user->setCodeSONEDE($data['CodeSONEDE']);
               $manager->persist($user);
               $manager->flush();

               $this->addFlash("success","Ajout des codes à éte effectuer avec success");

               return $this->redirectToRoute("profile", array("user"=>$user));
           }

           return $this->RenderForm("ProfileVide.html.twig", array("user"=>$user,"form"=>$form));
       }
       else{
           return $this->Render("Profile.html.twig", array("user"=>$user));
       }




    }


}