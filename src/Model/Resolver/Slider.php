<?php
/**
 * Scandiweb_SliderGraphQl
 *
 * @category    Scandiweb
 * @package     Scandiweb_SliderGraphQl
 * @author      Kriss Andrejevs <info@scandiweb.com>
 * @copyright   Copyright (c) 2018 Scandiweb, Ltd (https://scandiweb.com)
 */
declare(strict_types=1);

namespace ScandiPWA\SliderGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Scandiweb\Slider\Api\SliderRepositoryInterface;

/**
 * Class Slider
 * @package Scandiweb\SliderGraphQl\Model\Resolver
 */
class Slider implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * @var SliderRepositoryInterface
     */
    protected $sliderRepository;

    /**
     * Slider constructor.
     * @param ValueFactory $valueFactory
     * @param SliderRepositoryInterface $sliderRepository
     */
    public function __construct(
        ValueFactory $valueFactory,
        SliderRepositoryInterface $sliderRepository

    ) {
        $this->valueFactory = $valueFactory;
        $this->sliderRepository = $sliderRepository;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $result = function () {
            return null;
        };

        if (!isset($args['id'])) {
            return $this->valueFactory->create($result);
        }

        $id = $args['id'];
        try {
            /** @var \Scandiweb\Slider\Model\Slider $slider */
            $slider = $this->sliderRepository->get($id);
        } catch (NoSuchEntityException $e) {
            $result = function () use ($id) {
                return new GraphQlNoSuchEntityException(__(`Slider with id "{$id}" does not exist.`));
            };

            return $this->valueFactory->create($result);
        }

        $sliderData = $slider->getData();
        $sliderData['slides'] = [];
        $slides = $slider->getSlides();

        /** @var \Scandiweb\Slider\Model\Slide $slide */
        foreach ($slides as $slide) {
            $slideData = $slide->getData();
            $slideData['maps'] = $slide->getMaps();

            if ($slide->getFirstMobileImageLocation()) {
                $slideData[$slide::FIRST_MOBILE_IMAGE] = $slide->getFirstMobileImageUrl();
            }

            if ($slide->getFirstDesktopImageLocation()) {
                $slideData[$slide::FIRST_DESKTOP_IMAGE] = $slide->getFirstDesktopImageUrl();
            }

            if ($slide->getSecondMobileImageLocation()) {
                $slideData[$slide::SECOND_MOBILE_IMAGE] = $slide->getSecondMobileImageUrl();
            }

            if ($slide->getSecondDesktopImageLocation()) {
                $slideData[$slide::SECOND_DESKTOP_IMAGE] = $slide->getSecondDesktopImageUrl();
            }

            if ($slide->getThirdMobileImageLocation()) {
                $slideData[$slide::THIRD_MOBILE_IMAGE] = $slide->getThirdMobileImageUrl();
            }

            if ($slide->getThirdDesktopImageLocation()) {
                $slideData[$slide::THIRD_DESKTOP_IMAGE] = $slide->getThirdDesktopImageUrl();
            }

            $sliderData['slides'][] = $slideData;
        }

        $result = function () use ($sliderData) {
            return $sliderData;
        };

        return $this->valueFactory->create($result);
    }

}
