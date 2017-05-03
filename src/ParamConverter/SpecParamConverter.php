<?php

namespace Igdr\DoctrineSpecificationBundle\ParamConverter;

use App\Bundle\BaseBundle\Specification\BaseSpecInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Param converter for specification
 */
class SpecificationParamConverter implements ParamConverterInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @param \Doctrine\Common\Persistence\ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return !empty($configuration->getOptions()['spec']);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request                        $request
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration
     *
     * @return bool
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $configuration->getName();
        $class = $configuration->getClass();
        $options = $configuration->getOptions();
        $value = $request->attributes->get($name, false);
        if (null === $value) {
            $configuration->setIsOptional(true);
        }

        // find by identifier?
        if (false === $object = $this->find($class, $request, $options, $name)) {
            if ($value || false === $configuration->isOptional()) {
                throw new NotFoundHttpException(sprintf('%s object was not found.', $class));
            }
            $object = null;
        }

        $request->attributes->set($name, $object);

        return true;
    }

    /**
     * @param string  $class
     * @param Request $request
     * @param array   $options
     * @param string  $name
     *
     * @return bool
     */
    protected function find(string $class, Request $request, array $options, string $name)
    {
        $id = $this->getIdentifier($request, $options, $name);
        if (false === $id || null === $id) {
            return false;
        }

        $specification = $this->getSpecification($options);
        $specification->applyWhere();
        $specification->setId($id);

        try {
            return $this->doctrine->getManager()->getRepository($class)->matchSingleResult($specification);
        } catch (NoResultException $e) {
            return false;
        }
    }

    /**
     * @param array $options
     *
     * @return BaseSpecInterface
     */
    protected function getSpecification(array $options)
    {
        if (empty($options['spec'])) {
            throw new \LogicException(sprintf('Specification ("spec" parameter) MUST be set'));
        }

        $className = $options['spec'];
        if (false === class_exists($className)) {
            throw new \LogicException(sprintf('Specification class "%s" does not exist', $options['spec']));
        }

        $spec = new $className();
        if (!($spec instanceof BaseSpecInterface)) {
            throw new \LogicException(sprintf('Specification MUST implement the interface "%s"', BaseSpecInterface::class));
        }

        return $spec;
    }

    /**
     * @param Request $request
     * @param array   $options
     * @param string  $name
     *
     * @return array|bool|mixed
     */
    protected function getIdentifier(Request $request, array $options, string $name)
    {
        if (isset($options['id'])) {
            if (!is_array($options['id'])) {
                $name = $options['id'];
            } elseif (is_array($options['id'])) {
                $id = [];
                foreach ($options['id'] as $field) {
                    $id[$field] = $request->attributes->get($field);
                }

                return $id;
            }
        }

        if ($request->attributes->has($name)) {
            return $request->attributes->get($name);
        }

        if ($request->attributes->has('id') && !isset($options['id'])) {
            return $request->attributes->get('id');
        }

        return false;
    }
}
