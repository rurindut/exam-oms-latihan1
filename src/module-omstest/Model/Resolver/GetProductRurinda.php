<?php 
namespace Icube\OmsTest\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class GetProductRurinda implements ResolverInterface
{
    protected $productRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        ProductRepository $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize($args['pageSize'])
            ->setCurrentPage($args['currentPage'])
            ->addFilter('name', $args['search'], 'like')
            ->create();

        $products = $this->productRepository->getList($searchCriteria);

        $items = [];
        foreach ($products->getItems() as $product) {
            $dimension_package_height = $product->getCustomAttribute('dimension_package_height');
            $dimension_package_length = $product->getCustomAttribute('dimension_package_length');
            $dimension_package_width = $product->getCustomAttribute('dimension_package_width');

            $items[] = [
                'entity_id' => $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'status' => $product->getStatus(),
                'description' => $product->getDescription(),
                'short_description' => $product->getShortDescription(),
                'weight' => $product->getWeight(),
                'dimension_package_height' => ($dimension_package_height) ? $dimension_package_height->getValue() : 0,
                'dimension_package_length' => ($dimension_package_length) ? $dimension_package_length->getValue() : 0,
                'dimension_package_width' => ($dimension_package_width) ? $dimension_package_width->getValue() : 0,
            ];
        }

        return [
            'items' => $items,
            'page_info' => [
                'current_page' => $products->getSearchCriteria()->getCurrentPage(),
                'page_size' => $products->getSearchCriteria()->getPageSize(),
                'total_pages' => ceil($products->getTotalCount() / $args['pageSize']),
            ],
            'total_count' => $products->getTotalCount(),
        ];
    }
}
