<?php

namespace App\Controller\Api;

use App\Entity\User;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package App\Controller
 *
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController
{

    /**
     * @Route("/", name="index")
     */
    public function index(): Response {
        return new JsonResponse(['api_version' => '0.0.1']);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(): Response {
        return new Response("This is login");
    }

    /**
     * @Route("/debit", name="debit")
     * @param Request $request
     * @return Response
     */
    public function debit(Request $request): Response {
        if ($this->authorize($request)) {
            return $this->doTransaction($request, TransactionType::DEBIT);
        } else {
            $response = new JsonResponse(['status' => 'error', 'message' => "Accès non-autorisé"]);
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            return $response;
        }
    }

    /**
     * @Route("/credit", name="credit")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function credit(Request $request): Response {
        if ($this->authorize($request)) {
            return $this->doTransaction($request, TransactionType::CREDIT);
        } else {
            $response = new JsonResponse(['status' => 'error', 'message' => "Accès non-autorisé"]);
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            return $response;
        }
    }

    /**
     * @Route("/new-user", name="new_user", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function newUser(Request $request): Response {
        if ($this->authorize($request)) {
            $entityManager = $this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()->getRepository(User::class);

            $firstname = $request->query->get('firstname');
            $lastname = $request->query->get('lastname');
            $tagRfid = $request->query->get('tag_rfid');

            if (!empty($firstname) && !empty($lastname) && !empty($tagRfid)) {
                $existingUser = $repository->findOneBy(['tag_rfid' => $tagRfid]);
                if (!$existingUser) {
                    $user = new User();
                    $user
                        ->setFirstname($firstname)
                        ->setLastname($lastname)
                        ->setTagRfid($tagRfid)
                        ->setStatus('new');

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $response = new JsonResponse(['status' => 'success', 'message' => "$firstname $lastname a bien été enregistré avec le tag RFID $tagRfid"]);
                    $response->setStatusCode(Response::HTTP_CREATED);
                    return $response;
                } else {
                    $response = new JsonResponse(['status' => 'error', 'message' => "Le tag RFID est déjà utilisé"]);
                    $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                    return $response;
                }
            } else {
                $response = new JsonResponse(['status' => 'error', 'message' => "Paramètres incorrectes"]);
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                return $response;
            }
        } else {
            $response = new JsonResponse(['status' => 'error', 'message' => "Accès non-autorisé"]);
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            return $response;
        }
    }

    private function authorize(Request $request): bool {
        return false;
    }

    private function doTransaction(Request $request, int $type): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(User::class);

        $tagRfid = $request->query->get('tag_rfid');
        $amount = $request->query->get('amount');

        $admin = $this->getDoctrine()->getRepository(Admin::class)->findAll()[0];

        if (!empty($tagRfid) && !empty($amount) && is_numeric($amount)) {
            $user = $repository->findOneBy(['tag_rfid' => $tagRfid]);
            if ($user) {
                $transaction = new Transaction();
                if ($type === TransactionType::DEBIT)
                    $transaction->setAmount(-$amount);
                else if ($type === TransactionType::CREDIT)
                    $transaction->setAmount($amount);
                $transaction->setDate(new DateTime('now'));
                $transaction->setNumTerminal(1);
                $transaction->setUser($user);
                $transaction->setAdmin($admin);

                $entityManager->persist($transaction);
                $entityManager->flush();

                $response = new JsonResponse(['status' => 'success', 'message' => "Transaction effctuée avec succès"]);
                $response->setStatusCode(Response::HTTP_CREATED);
                return $response;
            } else {
                // User not found
                $response = new JsonResponse(['status' => 'error', 'message' => "L'utilisateur avec le tag RFID $tagRfid est introuvable"]);
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                return $response;
            }
        } else {
            $response = new JsonResponse(['status' => 'error', 'message' => "Paramètres incorrectes"]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            return $response;
        }
    }
}
