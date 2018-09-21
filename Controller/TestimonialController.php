<?php
namespace Puzzle\Admin\ExpertiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\ExpertiseBundle\Form\Type\TestimonialCreateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\TestimonialUpdateType;
use GuzzleHttp\Exception\BadResponseException;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;
use Puzzle\ConnectBundle\Service\PuzzleApiObjectManager;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class TestimonialController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['author', 'company', 'position', 'message'];
    }
    
	/***
	 * List testimonials
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
	 */
    public function listAction(Request $request) {
        try {
    		/** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
    		$apiClient = $this->get('puzzle_connect.api_client');
    		$testimonials = $apiClient->pull('/expertise/testimonials', null);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $testimonials = [];
        }
		
		return $this->render("PuzzleAdminExpertiseBundle:Testimonial:list.html.twig",['testimonials' => $testimonials]);
	}
	
    /***
     * Create a new testimonial
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request) {
        $data = PuzzleApiObjectManager::hydratate($this->fields, []);
        
        $form = $this->createForm(TestimonialCreateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_expertise_testimonial_create')
        ]);
        $form->handleRequest($request);
            
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            try {
                /** @var Puzzle\Admin\MediaBundle\Service\UploadManager $uploader */
                $uploader = $this->get('admin.media.upload_manager');
                $uploads = $uploader->prepareUpload($_FILES, $request->getSchemeAndHttpHost());
                
                $postData = $form->getData();
                $postData['picture'] = $uploads && count($uploads) > 0 ? $uploads[0] : $postData['file-url'] ?? null;
                
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $testimonial = $apiClient->push('post', '/expertise/testimonials', $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
                
                return $this->redirectToRoute('admin_expertise_project_update', array('id' => $testimonial['id']));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $event = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                if ($request->isXmlHttpRequest() === true) {
                    return $event->getResponse();
                }
                
                return $this->redirectToRoute('admin_expertise_testimonial_list');
            }
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Testimonial:create.html.twig", ['form' => $form->createView()]);
    }
    
    /***
     * Show testimonial
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function showAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $testimonial = $apiClient->pull('/expertise/testimonials/'.$id);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $testimonial = [];
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Service:show.html.twig", array(
            'testimonial' => $testimonial
        ));
    }
    
    /***
     * Update testimonial
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id) {
        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        $data = [];
        
        try {
            $testimonial = $apiClient->pull('/expertise/projects/'.$id);
            $data = PuzzleApiObjectManager::hydratate($this->fields, $project);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $testimonial = [];
        }
        
        $form = $this->createForm(TestimonialUpdateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_expertise_testimonial_update', ['id' => $id])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            try {
                /** @var Puzzle\Admin\MediaBundle\Service\UploadManager $uploader */
                $uploader = $this->get('admin.media.upload_manager');
                $uploads = $uploader->prepareUpload($_FILES, $request->getSchemeAndHttpHost());
                
                $postData = $form->getData();
                $postData['picture'] = $uploads && count($uploads) > 0 ? $uploads[0] : $postData['file-url'] ?? null;
                
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $testimonial = $apiClient->push('put', '/expertise/testimonials/'.$id, $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.put', [], 'success'));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $event = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                if ($request->isXmlHttpRequest() === true) {
                    return $event->getResponse();
                }
            }
            
            return $this->redirectToRoute('admin_expertise_project_update', array('id' => $id));
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Testimonial:update.html.twig", [
            'testimonial' => $testimonial,
            'form' => $form->createView()
        ]);
    }
    
    /***
     * Remove testimonial
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id){
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $response = $apiClient->push('delete', '/expertise/testimonials/'.$id);
            
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
        
        return $this->redirectToRoute('admin_expertise_testimonial_list');
    }
}
