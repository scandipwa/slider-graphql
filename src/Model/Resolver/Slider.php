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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

use Scandiweb\Slider\Model\ResourceModel\Slider\CollectionFactory as SliderCollectionFactory;
use Scandiweb\Slider\Model\ResourceModel\Slide\CollectionFactory as SlideCollectionFactory;
use Scandiweb\Slider\Model\ResourceModel\Map\CollectionFactory as MapCollectionFactory;

/**
 * Class Slider
 * @package Scandiweb\SliderGraphQl\Model\Resolver
 */
class Slider implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var \Scandiweb\Slider\Model\ResourceModel\Slider\CollectionFactory
     */
    protected $sliderCollectionFactory;

    /**
     * @var \Scandiweb\Slider\Model\ResourceModel\Slide\CollectionFactory
     */
    protected $slideCollectionFactory;

    /**
     * @var \Scandiweb\Slider\Model\ResourceModel\Map\CollectionFactory
     */
    protected $mapCollectionFactory;

    /**
     * Slider constructor.
     * @param ValueFactory $valueFactory
     * @param \Scandiweb\Slider\Model\ResourceModel\Slider\CollectionFactory $sliderCollectionFactory
     * @param \Scandiweb\Slider\Model\ResourceModel\Slide\CollectionFactory $slideCollectionFactory
     * @param \Scandiweb\Slider\Model\ResourceModel\Map\CollectionFactory $mapCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        ValueFactory $valueFactory,
        SliderCollectionFactory $sliderCollectionFactory,
        SlideCollectionFactory $slideCollectionFactory,
        MapCollectionFactory $mapCollectionFactory

    ) {
        $this->valueFactory = $valueFactory;
        $this->sliderCollectionFactory = $sliderCollectionFactory;
        $this->slideCollectionFactory = $slideCollectionFactory;
        $this->mapCollectionFactory = $mapCollectionFactory;
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

        if (isset($args['id'])) {

            $slider = $this->sliderCollectionFactory->create();
            $slider->addFieldToFilter('slider_id', $args['id'])->load();
            $sliderData = $slider->getFirstItem()->getData();

            $slides = $this->slideCollectionFactory->create();
            $slides->addSliderFilter($args['id'])
                ->addStoreFilter()
                ->addDateFilter()
                ->addIsActiveFilter()
                ->addPositionOrder();

            $sliderData['slides'] = $slides->getData();

            $maps = $this->mapCollectionFactory->create();
            $maps = $maps->addSliderFilter($args['id'])
                ->addIsActiveFilter()
                ->getItems();

            foreach ($sliderData['slides'] as &$slide) {
                if (array_key_exists('mobile_image', $slide) && isset($slide['mobile_image'])){
                    $slide['mobile_image'] = DirectoryList::MEDIA . DIRECTORY_SEPARATOR . $slide['mobile_image'];
                }
                if (array_key_exists('desktop_image', $slide) && isset($slide['desktop_image'])){
                    $slide['desktop_image'] = DirectoryList::MEDIA . DIRECTORY_SEPARATOR . $slide['desktop_image'];
                }
                foreach ($maps as $map) {
                    if ($map['slide_id'] === $slide['slide_id']) {
                        $slide['maps'][] = $map;
                    }
                }
            }
            unset ($slide);

            if ($sliderData) {
                $result = function () use ($sliderData) {
                    return $sliderData;
                };
            }
        }

        return $this->valueFactory->create($result);
    }

}
