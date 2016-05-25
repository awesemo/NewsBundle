<?php

namespace Rz\NewsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddNewsPageCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('rz.news.enable_controller')) {
            $this->attachNewsPage($container);
        }

        $this->attachSiteSettings($container);
        $this->attachSeoSettings($container);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function attachSiteSettings(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('sonata.news.admin.post');
        $definition->addMethodCall('setSiteManager', array(new Reference('sonata.page.manager.site')));
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function attachSeoSettings(ContainerBuilder $container)
    {
        //SEO Provider
        $defaultProvider = $container->getParameter('rz.news.default_seo_provider');
        $seoProvider = $container->getDefinition($defaultProvider);
        $seoProvider->addMethodCall('setPostManager', array(new Reference('sonata.news.manager.post')));
        $seoProvider->addMethodCall('setMediaAdmin', array(new Reference('sonata.media.admin.media')));
        $seoProvider->addMethodCall('setMediaManager', array(new Reference('sonata.media.manager.media')));
        $seoProvider->addMethodCall('setMetatagChoices', array($container->getParameter('rz_seo.metatags')));

        $definition = $container->getDefinition('sonata.news.admin.post');
        $definition->addMethodCall('setSeoProvider', array(new Reference($defaultProvider)));
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function attachNewsPage(ContainerBuilder $container)
    {
        $transformer = $container->getDefinition('rz.news.transformer');
        #set slugify service
        $serviceId = $container->getParameter('rz.news.slugify_service');
        $transformer->addMethodCall('setSlugify', array(new Reference($serviceId)));
        #set default values
        $transformer->addMethodCall('setPermalink', array(new Reference('rz.news.permalink.default')));
        $transformer->addMethodCall('setCategoryPermalink', array(new Reference('rz.news.permalink.category')));
        $transformer->addMethodCall('setDefaultNewsPageSlug', array($container->getParameter('rz.news.news_page_parent_slug')));
        $transformer->addMethodCall('setPostBlockService', array($container->getParameter('rz.news.post_block_service')));
        $transformer->addMethodCall('setPageServices', array($container->getParameter('rz.news.page.services')));

        ########################################
        ## Inject Transformer to PostAdmin
        ########################################
        $definition = $container->getDefinition('sonata.news.admin.post');
        $definition->addMethodCall('setTransformer', array(new Reference('rz.news.transformer')));

    }
}
