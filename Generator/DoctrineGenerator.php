<?php

namespace Melody\AdminBundle\Generator;

use Admingenerator\GeneratorBundle\Generator\DoctrineGenerator as AdminDoctrineGenerator;
use Melody\AdminBundle\Builder\Generator as AdminGenerator;
use Admingenerator\GeneratorBundle\Exception\CantGenerateException;

class DoctrineGenerator extends AdminDoctrineGenerator {
    
    /**
     * (non-PHPdoc)
     * @see Generator/Admingenerator\GeneratorBundle\Generator.Generator::doBuild()
     */
    protected function doBuild()
    {
        $this->validateYaml();
    
        $generator = new AdminGenerator($this->cache_dir, $this->getGeneratorYml());
        $generator->setBundleConfig($this->bundleConfig);
        $generator->setRouter($this->router);
        $generator->setBaseAdminTemplate(
            $generator->getFromYaml(
                'base_admin_template',
                $generator->getFromBundleConfig('base_admin_template')
            )
        );
        $generator->setFieldGuesser($this->getFieldGuesser());
        $generator->setMustOverwriteIfExists($this->needToOverwrite($generator));
        $generator->setTwigExtensions($this->twig->getExtensions());
        $generator->setTwigFilters($this->twig->getFilters());
        $generator->setTemplateDirs($this->templatesDirectories);
        $generator->setBaseController(
            'Admingenerator\GeneratorBundle\Controller\Doctrine\BaseController'
        );
        $generator->setBaseGeneratorName($this->getBaseGeneratorName());
    
        $embed_types = $generator->getFromYaml('params.embed_types', array());
    
        foreach ($embed_types as $yaml_path) {
            $this->prebuildEmbedType($yaml_path, $generator);
        }
    
        $builders = $generator->getFromYaml('builders', array());
    
        foreach( array(
            'list'=>array('ListBuilderAction','ListBuilderTemplate','FiltersBuilderType'),
            'nested_list'=>array('NestedListBuilderAction','NestedListBuilderTemplate'),
            'edit'=>array('EditBuilderAction','EditBuilderTemplate','EditBuilderType'),
            'new'=>array('NewBuilderAction','NewBuilderTemplate','NewBuilderType'),
            'show'=>array('ShowBuilderAction','ShowBuilderTemplate'),
            'excel'=>array('ExcelBuilderAction'),
            'actions'=>array('ActionsBuilderAction','ActionsBuilderTemplate'),
        ) as $key => $builderClasses ) {
            if (array_key_exists($key, $builders)) {
                foreach( $builderClasses as $builderClass ) {
                    $builderClassWithNameSpace = '\\Admingenerator\\GeneratorBundle\\Builder\\Doctrine\\'.$builderClass;
                    $builder = new $builderClassWithNameSpace();
                    $generator->addBuilder($builder);
                }
            }
        }
    
        $generator->writeOnDisk(
            $this->getCachePath(
                $generator->getFromYaml('params.namespace_prefix'),
                $generator->getFromYaml('params.bundle_name')
            )
        );
    }
    
}
