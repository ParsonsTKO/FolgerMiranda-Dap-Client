<?php

namespace DAPClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use DAPClientBundle\Services\ApiSessionService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ApiSessionController extends Controller
{

    /**
     * Callback to create session
     * @param $request \Symfony\Component\HttpFoundation\Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function callbackAction(Request $request, ApiSessionService $apiSession)
    {
        try {
            $userData = $apiSession->getUserCallbackData($request->query);

            $response = $this->redirectToRoute('dap_client_homepage');
            $startDate = time();
            $cookie = new Cookie('userData', $userData, date('Y-m-d H:i:s', strtotime('+1 day', $startDate)), '/');
            $response->headers->setCookie($cookie);
            return $response;
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Could not login: '.$e->getMessage());
        }
    }
    public function logoutAction(Request $request)
    {
        try {
            $response = $this->redirectToRoute('dap_client_homepage');
            $response->headers->clearCookie('userData', '/');

            return $response;
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Could not Logout: '.$e->getMessage());
        }
    }
    /**
     * @return RedirectResponse
     */
    public function profileAction(Request $request, ApiSessionService $apiSession)
    {
        $apiSessionEndpoint = $this->getParameter('dap_client.header')['endpoint_apisession'];
        try {
            $userData = $apiSession->getUserSession($request->cookies);
        } catch (\Exception $e) {
            $userData = null;
        }

        return new RedirectResponse(sprintf(
            '%s/profile?_redirect=client&api-key=%s',
            $apiSessionEndpoint,
            $userData->apikey ?: ''
        ));
    }
}
