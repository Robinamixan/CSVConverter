<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 16.2.18
 * Time: 12.15
 */

namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class TranslateController extends Controller
{
    /**
     * Matches / exactly
     *
     * @Route("/translate", name="test_translate")
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return Response
     */
    public function translateAction(TranslatorInterface $translator)
    {
        $temp = $translator->trans('Symfony is great');

        return $this->render('TestTranslation/test_translation.html.twig', array(
            'translated_text' => $temp,
        ));
    }
}