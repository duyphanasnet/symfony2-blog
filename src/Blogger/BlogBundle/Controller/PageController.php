<?php
// src/Blogger/BlogBundle/Controller/PageController.php

namespace Blogger\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Import new namespaces
use Blogger\BlogBundle\Entity\Enquiry;
use Blogger\BlogBundle\Form\EnquiryType;

class PageController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()
                   ->getManager();

        $blogs = $em->getRepository('BlogBundle:Blog')
                    ->getLatestBlogs();

        return $this->render('BlogBundle:Page:index.html.twig', array(
            'blogs' => $blogs
        ));
    }

    public function aboutAction()
    {
        return $this->render('BlogBundle:Page:about.html.twig');
    }

    public function contactAction()
	{
	    $enquiry = new Enquiry();
	    $form = $this->createForm(new EnquiryType(), $enquiry);

	    $request = $this->getRequest();
	    if ($request->getMethod() == 'POST') {
	        $form->bind($request);

	        if ($form->isValid()) {

		         $message = \Swift_Message::newInstance()
		            ->setSubject('Contact enquiry from symblog')
		            ->setFrom('enquiries@symblog.co.uk')
		            ->setTo($this->container->getParameter('blogger_blog.emails.contact_email'))
		            ->setBody($this->renderView('BlogBundle:Page:contactEmail.txt.twig', array('enquiry' => $enquiry)));
		        $this->get('mailer')->send($message);

		        $this->get('session')->getFlashBag()->add('blogger-notice', 'Your contact enquiry was successfully sent. Thank you!');

		        // Redirect - This is important to prevent users re-posting
		        // the form if they refresh the page
		        return $this->redirect($this->generateUrl('BloggerBlogBundle_contact'));
		    }
	    }

	    return $this->render('BlogBundle:Page:contact.html.twig', array(
	        'form' => $form->createView()
	    ));
	}

	public function sidebarAction()
	{
	    $em = $this->getDoctrine()
	               ->getManager();

	    $tags = $em->getRepository('BlogBundle:Blog')
	               ->getTags();

	    $tagWeights = $em->getRepository('BlogBundle:Blog')
	                     ->getTagWeights($tags);

	    $commentLimit   = $this->container
                           ->getParameter('blogger_blog.comments.latest_comment_limit');
	    $latestComments = $em->getRepository('BlogBundle:Comment')
	                         ->getLatestComments($commentLimit);

	    return $this->render('BlogBundle:Page:sidebar.html.twig', array(
	        'latestComments'    => $latestComments,
	        'tags'              => $tagWeights
	    ));
	}
}