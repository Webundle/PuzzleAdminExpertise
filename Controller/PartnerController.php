<?php
namespace Puzzle\Admin\ExpertiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ServiceCreateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ServiceUpdateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\PartnerCreateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\PartnerUpdateType;
use Puzzle\ConnectBundle\Service\PuzzleApiObjectManager;
use GuzzleHttp\Exception\BadResponseException;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class PartnerController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['name', 'location'];
    }
    
	/***
	 * List partners
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
	 */
    public function listAction(Request $request) {
		try {
    		/** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
    		$apiClient = $this->get('puzzle_connect.api_client');
    		$partners = $apiClient->pull('/expertise/partners', null);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $partners = [];
        }
		
		return $this->render("PuzzleAdminExpertiseBundle:Partner:list.html.twig",['partners' => $partners]);
	}
	
    /***
     * Create a new partner
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request) {
        $data = PuzzleApiObjectManager::hydratate($this->fields, []);
        
        $form = $this->createForm(PartnerCreateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_expertise_partner_create')
        ]);
        $form->handleRequest($request);
            
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            try {
                /** @var Puzzle\Admin\MediaBundle\Service\UploadManager $uploader */
                $uploader = $this->get('admin.media.upload_manager');
                $uploads = $uploader->prepareUpload($_FILES, $request->getSchemeAndHttpHost());
                
                $postData = $form->getData();
                $postData['picture'] = $uploads && count($uploads) > 0 ? $uploads[0] : $postData['file-url'] ?? null;
                $postData = PuzzleApiObjectManager::sanitize($postData);
                
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $apiClient->push('post', '/expertise/partners', $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $event = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            }
            
            return $this->redirectToRoute('admin_expertise_partner_list');
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Partner:create.html.twig", ['form' => $form->createView()]);
    }
    
    /***
     * Show partner
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function showAction(Request $request, $id) {
        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        $partner = $apiClient->pull('/expertise/partners/'.$id);
        
        $parent = null;
        if (isset($partner['_embedded'])) {
            $parent = $partner['_embedded']['parent'] ?? null;
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Service:show.html.twig", array(
            'partner' => $partner,
            'parent' => $parent
        ));
    }
    
    /***
     * Update partner
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id) {
        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        $partner = $apiClient->pull('/expertise/partners/'.$id);
        
        $data = ['name'  => $partner['name'], 'location' => $partner['location']];
        
        $form = $this->createForm(PartnerUpdateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_expertise_partner_update', ['id' => $id])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            try {
                /** @var Puzzle\Admin\MediaBundle\Service\UploadManager $uploader */
                $uploader = $this->get('admin.media.upload_manager');
                $uploads = $uploader->prepareUpload($_FILES, $request->getSchemeAndHttpHost());
                
                $postData = $form->getData();
                $postData['picture'] = $uploads && count($uploads) > 0 ? $uploads[0] : $postData['file-url'] ?? null;
                $postData = PuzzleApiObjectManager::sanitize($postData);
                
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $apiClient->push('put', '/expertise/partners/'.$id, $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.put', [], 'success'));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $event = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            }
            
            return $this->redirectToRoute('admin_expertise_partner_list');
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Partner:update.html.twig", [
            'partner' => $partner,
            'form' => $form->createView()
        ]);
    }
    
    /***
     * Remove partner
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id){
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $response = $apiClient->push('delete', '/expertise/partners/'.$id);
            
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
        
        
        return $this->redirectToRoute('admin_expertise_partner_list');
    }
}
