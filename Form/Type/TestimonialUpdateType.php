<?php
namespace Puzzle\Admin\ExpertiseBundle\Form\Type;

use Puzzle\Admin\ExpertiseBundle\Form\Model\AbstractTestimonialType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class TestimonialUpdateType extends AbstractTestimonialType
{
	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		
		$resolver->setDefault('csrf_token_id', 'testimonial_update');
		$resolver->setDefault('validation_groups', ['Update']);
	}
	
	public function getBlockPrefix() {
		return 'admin_expertise_testimonial_update';
	}
}
