<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Repository\FetcherConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\ServiceProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_USER")
 */
class FetcherController extends AbstractController
{
    private ServiceProviderInterface $serviceProvider;
    private FetcherConfigurationRepository $fetcherConfigurationRepository;
    private EntityManagerInterface $em;
    private FormFactoryInterface $formFactory;
    private TranslatorInterface $translator;

    public function __construct(ServiceProviderInterface $serviceProvider, FetcherConfigurationRepository $fetcherConfigurationRepository, EntityManagerInterface $em, FormFactoryInterface $formFactory, TranslatorInterface $translator)
    {
        $this->serviceProvider = $serviceProvider;
        $this->fetcherConfigurationRepository = $fetcherConfigurationRepository;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
    }

    /**
     * @Route(path="/admin/fetcher", name="fetcher_list")
     */
    public function list(): Response
    {
        $fetchersConfig = [];

        foreach ($this->serviceProvider->getProvidedServices() as $fetcher) {
            $name = (new \ReflectionClass($fetcher))->getShortName();
            $fetchersConfig[] = $this->fetcherConfigurationRepository->findOneOrCreate($name);
        }

        return $this->render('admin/fetchers/list.html.twig', [
            'fetchers' => $fetchersConfig,
        ]);
    }

    /**
     * @Route(path="/admin/fetcher/edit/{fetcherShortClass}", name="fetcher_edit")
     */
    public function edit(string $fetcherShortClass, Request $request): Response
    {
        $class = sprintf('App\Fetcher\%s', $fetcherShortClass);

        if (!$this->serviceProvider->has($class)) {
            throw $this->createNotFoundException("The fetcher '$fetcherShortClass' is not found.");
        }

        $fetcherConfiguration = $this->fetcherConfigurationRepository->findOneOrCreate($fetcherShortClass);
        $fetcher = $this->serviceProvider->get($class);
        $configurationBuilder = $this->formFactory->createNamedBuilder('configuration');
        $fetcher->configureForm($configurationBuilder);

        $form = $this->createFormBuilder($fetcherConfiguration)
            ->add('active', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
            ])
            ->add($configurationBuilder)
            ->getForm();

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->persist($fetcherConfiguration);
            $this->em->flush();

            $successMessage = $this->translator->trans('fetcher.edit.success', [], 'admin');
            $this->addFlash('success', $successMessage);

            return $this->redirectToRoute('fetcher_list');
        }

        return $this->render('admin/fetchers/edit.html.twig', [
            'form' => $form->createView(),
            'fetcherConfiguration' => $fetcherConfiguration,
        ]);
    }

    /**
     * @Route(path="/admin/fetcher/active/{fetcher}/{value}", name="fetcher_toggle_active", methods={"GET"})
     */
    public function toggleActive(string $fetcher, string $value, Request $request): Response
    {
        $value = $request->attributes->getBoolean('value');
        $class = sprintf('App\Fetcher\%s', $fetcher);

        if (!$this->serviceProvider->has($class)) {
            throw $this->createNotFoundException("Required fetcher $fetcher not found");
        }

        $fetcherConfig = $this->fetcherConfigurationRepository->findOneOrCreate($fetcher);
        $fetcherConfig->setActive($value);
        $this->em->persist($fetcherConfig);
        $this->em->flush();

        return $this->redirectToRoute('fetcher_list');
    }
}
