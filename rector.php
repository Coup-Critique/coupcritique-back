<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php73\Rector\BooleanOr\IsCountableRector;
use Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Doctrine\Set\DoctrineSetList;
// use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;
use Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
// use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    $rectorConfig->skip([
        IsCountableRector::class,
        // CountOnNullRector::class,
        StringifyStrNeedlesRector::class, 
        NullToStrictStringFuncCallArgRector::class,
        JsonThrowOnErrorRector::class,
        // AddDefaultValueForUndefinedVariableRector::class,
        ChangeSwitchToMatchRector::class,
        ThisCallOnStaticMethodToStaticCallRector::class,
        RemoveExtraParametersRector::class,
        AddLiteralSeparatorToNumberRector::class,
        BinaryOpBetweenNumberAndStringRector::class,
        // TEMP :
        // InlineConstructorDefaultToPropertyRector::class,
        // __DIR__ . '/src/Service/DataManager/Abstracts/AbstractDataManager.php',
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        SetList::PHP_82,
        SymfonySetList::SYMFONY_62,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);

    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        new AnnotationToAttribute('OpenApi\\Annotations\\AdditionalProperties', 'OpenApi\\Attributes\\AdditionalProperties'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Attachable', 'OpenApi\\Attributes\\Attachable'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Components', 'OpenApi\\Attributes\\Components'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Contact', 'OpenApi\\Attributes\\Contact'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Delete', 'OpenApi\\Attributes\\Delete'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Discriminator', 'OpenApi\\Attributes\\Discriminator'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Examples', 'OpenApi\\Attributes\\Examples'),
        new AnnotationToAttribute('OpenApi\\Annotations\\ExternalDocumentation', 'OpenApi\\Attributes\\ExternalDocumentation'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Flow', 'OpenApi\\Attributes\\Flow'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Get', 'OpenApi\\Attributes\\Get'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Head', 'OpenApi\\Attributes\\Head'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Header', 'OpenApi\\Attributes\\Header'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Info', 'OpenApi\\Attributes\\Info'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Items', 'OpenApi\\Attributes\\Items'),
        new AnnotationToAttribute('OpenApi\\Annotations\\JsonContent', 'OpenApi\\Attributes\\JsonContent'),
        new AnnotationToAttribute('OpenApi\\Annotations\\License', 'OpenApi\\Attributes\\License'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Link', 'OpenApi\\Attributes\\Link'),
        new AnnotationToAttribute('OpenApi\\Annotations\\MediaType', 'OpenApi\\Attributes\\MediaType'),
        new AnnotationToAttribute('OpenApi\\Annotations\\OpenApi', 'OpenApi\\Attributes\\OpenApi'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Operation', 'OpenApi\\Attributes\\Operation'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Options', 'OpenApi\\Attributes\\Options'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Parameter', 'OpenApi\\Attributes\\Parameter'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Patch', 'OpenApi\\Attributes\\Patch'),
        new AnnotationToAttribute('OpenApi\\Annotations\\PatchItem', 'OpenApi\\Attributes\\PatchItem'),
        new AnnotationToAttribute('OpenApi\\Annotations\\PathParameter', 'OpenApi\\Attributes\\PathParameter'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Post', 'OpenApi\\Attributes\\Post'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Property', 'OpenApi\\Attributes\\Property'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Put', 'OpenApi\\Attributes\\Put'),
        new AnnotationToAttribute('OpenApi\\Annotations\\RequestBody', 'OpenApi\\Attributes\\RequestBody'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Response', 'OpenApi\\Attributes\\Response'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Schema', 'OpenApi\\Attributes\\Schema'),
        new AnnotationToAttribute('OpenApi\\Annotations\\SecurityScheme', 'OpenApi\\Attributes\\SecurityScheme'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Server', 'OpenApi\\Attributes\\Server'),
        new AnnotationToAttribute('OpenApi\\Annotations\\ServerVariable', 'OpenApi\\Attributes\\ServerVariable'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Tag', 'OpenApi\\Attributes\\Tag'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Trace', 'OpenApi\\Attributes\\Trace'),
        new AnnotationToAttribute('OpenApi\\Annotations\\Xml', 'OpenApi\\Attributes\\Xml'),
        new AnnotationToAttribute('OpenApi\\Annotations\\XmlContent', 'OpenApi\\Attributes\\XmlContent'),
    ]);
};
