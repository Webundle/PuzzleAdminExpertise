<?php
namespace Puzzle\Admin\ExpertiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ServiceCreateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ServiceUpdateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\FaqCreateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\FaqUpdateType;
use GuzzleHttp\Exception\BadResponseException;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;
use Puzzle\ConnectBundle\Service\PuzzleApiObjectManager;
use Puzzle\ConnectBundle\Service\ErrorFactory;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class FaqController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['question', 'answer'];
    }
    
	/***
	 * List faqs
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
	 */
    public function listAction(Request $request) {
        try {
    		/** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
    		$apiClient = $this->get('puzzle_connect.api_client');
    		$faqs = $apiClient->pull('/expertise/faqs', null);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $faqs = [];
        }
		
		return $this->render("PuzzleAdminExpertiseBundle:Faq:list.html.twig",['faqs' => $faqs]);
	}
	
    /***
     * Create a new faq
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request) {
        $data = ['question'  => '', 'answer' => ''];
        
        $form = $this->createForm(FaqCreateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_expertise_faq_create')
        ]);
        $form->handleRequest($request);
            
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            try {
                $postData = $form->getData();
                $postData = PuzzleApiObjectManager::sanitize($postData);
                
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $faq = $apiClient->push('post', '/expertise/faqs', $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
                
                return $this->redirectToRoute('admin_expertise_faq_update', array('id' => $faq['id']));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                $form = ErrorFactory::createFormError($form, $e);
            }
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Faq:create.html.twig", ['form' => $form->createView()]);
    }
    
    /***
     * Show faq
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function showAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $faq = $apiClient->pull('/expertise/faqs/'.$id);
            
            $parent = null;
            if (isset($faq['_embedded'])) {
                $parent = $faq['_embedded']['parent'] ?? null;
            }
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $faq = $parent = [];
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Service:show.html.twig", array(
            'faq' => $faq,
            'parent' => $parent
        ));
    }
    
    /***
     * Update faq
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id) {
        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        
        try {
            $faq = $apiClient->pull('/expertise/faqs/'.$id);
            $data = PuzzleApiObjectManager::hydratate($this->fields, $faq);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $faq = [];
        }
        
        $form = $this->createForm(FaqUpdateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_expertise_faq_update', ['id' => $id])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            try {
                $postData = $form->getData();
                $postData = PuzzleApiObjectManager::sanitize($postData);
                
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $apiClient->push('put', '/expertise/faqs/'.$faq['id'], $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.put', [], 'success'));
                
                return $this->redirectToRoute('admin_expertise_faq_update', array('id' => $id));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                $form = ErrorFactory::createFormError($form, $e);
            }
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Faq:update.html.twig", [
            'faq' => $faq,
            'form' => $form->createView()
        ]);
    }
    
    /***
     * Remove faq
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
        	$response = $apiClient->push('delete', '/expertise/faqs/'.$id);
        	
        	if ($request->isXmlHttpRequest()) {
        	    return new JsonResponse($response);
        	}
        	
        	$this->addFlash('success', $this->get('translator')->trans('message.delete', [], 'success'));
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $event  = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            
            if ($request->isXmlHttpRequest()) {
                return $event->getResponse();
            }
        }
        
        return $this->redirectToRoute('admin_expertise_faq_list');
    }
}
