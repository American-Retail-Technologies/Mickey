<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Test\Unit\Model\Plugin;

use Aheadworks\Blog\Model\Plugin\Product;
use Aheadworks\Blog\Model\Rule\Condition\Product\Attributes as BlogProductAttributes;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Aheadworks\Blog\Model\Indexer\ProductPost\Processor as ProductPostProcessor;
use Magento\Framework\Indexer\StateInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Blog\Model\Plugin\Product
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Product
     */
    private $model;

    /**
     * @var BlogProductAttributes|\PHPUnit_Framework_MockObject_MockObject
     */
    private $blogProductAttributesMock;

    /**
     * @var ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerStateMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->blogProductAttributesMock = $this->getMockBuilder(BlogProductAttributes::class)
            ->setMethods(['loadAttributeOptions', 'getAttributeOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->indexerStateMock = $this->getMockForAbstractClass(
            StateInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['save']
        );

        $this->model = $objectManager->getObject(
            Product::class,
            [
                'blogProductAttributes' => $this->blogProductAttributesMock,
                'productRepository' => $this->productRepositoryMock,
                'indexerState' => $this->indexerStateMock
            ]
        );
    }

    /**
     * Testing of beforeSave method
     */
    public function testBeforeSave()
    {
        $productId = 1;
        $productClimate = ['All-Weather', 'Cold'];
        $attributeOption = ['climate' => 'Climate'];

        $productMock = $this->getMockBuilder(ProductModel::class)
            ->setMethods(['getId', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->once())
            ->method('getData')
            ->with('climate')
            ->willReturn($productClimate);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);
        $this->blogProductAttributesMock->expects($this->once())
            ->method('loadAttributeOptions')
            ->willReturnSelf();
        $this->blogProductAttributesMock->expects($this->once())
            ->method('getAttributeOption')
            ->willReturn($attributeOption);

        $this->model->beforeSave($productMock);
    }

    /**
     * Testing of afterSave method
     */
    public function testAfterSave()
    {
        $productClimate = ['All-Weather', 'Cold'];
        $attributeOption = ['climate' => 'Climate'];

        $productMock = $this->getMockBuilder(ProductModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getData')
            ->with('climate')
            ->willReturn($productClimate);
        $this->blogProductAttributesMock->expects($this->once())
            ->method('loadAttributeOptions')
            ->willReturnSelf();
        $this->blogProductAttributesMock->expects($this->once())
            ->method('getAttributeOption')
            ->willReturn($attributeOption);
        $this->indexerStateMock->expects($this->once())
            ->method('loadByIndexer')
            ->with(ProductPostProcessor::INDEXER_ID)
            ->willReturnSelf();
        $this->indexerStateMock->expects($this->once())
            ->method('setStatus')
            ->with(StateInterface::STATUS_INVALID)
            ->willReturnSelf();
        $this->indexerStateMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->model->afterSave($productMock, $productMock);
    }
}
