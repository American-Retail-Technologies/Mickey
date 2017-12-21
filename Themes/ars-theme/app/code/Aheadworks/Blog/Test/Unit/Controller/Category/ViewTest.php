<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Test\Unit\Controller\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Controller\Category\View;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\Message\ManagerInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\Action\Context;

/**
 * Test for \Aheadworks\Blog\Controller\Category\View
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const CATEGORY_ID = 1;
    const CATEGORY_STATUS = 1;
    const CATEGORY_NAME = 'Category';
    const CATEGORY_META_DESCRIPTION = 'Meta description';
    const STORE_ID = 1;
    const REFERER_URL = 'http://localhost';
    /**#@-*/

    /**
     * @var array
     */
    private $categoryStoreId = [self::STORE_ID, 2];

    /**
     * @var View
     */
    private $action;

    /**
     * @var Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageMock;

    /**
     * @var Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    private $forwardMock;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageConfigMock;

    /**
     * @var Title|\PHPUnit_Framework_MockObject_MockObject
     */
    private $titleMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->titleMock = $this->getMockBuilder(Title::class)
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getTitle', 'setMetadata'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($this->titleMock));
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->setMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($this->pageConfigMock));
        $resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->resultPageMock));

        $this->forwardMock = $this->getMockBuilder(Forward::class)
            ->setMethods(['setModule', 'setController', 'forward'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardMock->expects($this->any())
            ->method('setModule')
            ->will($this->returnSelf());
        $this->forwardMock->expects($this->any())
            ->method('setController')
            ->will($this->returnSelf());
        $this->forwardMock->expects($this->any())
            ->method('forward')
            ->will($this->returnSelf());
        $resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultForwardFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->forwardMock));

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::STORE_ID));
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));

        $this->categoryMock = $this->getMockForAbstractClass(CategoryInterface::class);
        $this->categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $this->categoryRepositoryMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo(self::CATEGORY_ID))
            ->will($this->returnValue($this->categoryMock));

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->resultRedirectMock));

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('blog_category_id'))
            ->will($this->returnValue(self::CATEGORY_ID));
        $redirectMock = $this->getMockForAbstractClass(RedirectInterface::class);
        $redirectMock->expects($this->any())
            ->method('getRefererUrl')
            ->will($this->returnValue(self::REFERER_URL));
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $requestMock,
                'redirect' => $redirectMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $resultRedirectFactoryMock
            ]
        );

        $this->action = $objectManager->getObject(
            View::class,
            [
                'context' => $context,
                'resultPageFactory' => $resultPageFactoryMock,
                'resultForwardFactory' => $resultForwardFactoryMock,
                'storeManager' => $storeManagerMock,
                'categoryRepository' => $this->categoryRepositoryMock
            ]
        );
    }

    /**
     * Prepare category mock
     *
     * @param int $status
     * @param array|null $storeId
     */
    private function prepareCategoryMock($status = self::CATEGORY_STATUS, $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->categoryStoreId;
        }
        $this->categoryMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CATEGORY_ID));
        $this->categoryMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::CATEGORY_NAME));
        $this->categoryMock->expects($this->any())
            ->method('getMetaDescription')
            ->will($this->returnValue(self::CATEGORY_META_DESCRIPTION));
        $this->categoryMock->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue($status));
        $this->categoryMock->expects($this->any())
            ->method('getStoreIds')
            ->will($this->returnValue($storeId));
    }

    /**
     * Testing return value of execute method
     */
    public function testExecuteResult()
    {
        $this->prepareCategoryMock();
        $this->assertSame($this->resultPageMock, $this->action->execute());
    }

    /**
     * Testing redirect if error is occur
     */
    public function testExecuteErrorRedirect()
    {
        $this->prepareCategoryMock();
        $this->categoryRepositoryMock->expects($this->any())
            ->method('get')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(__('Not found.'))
            );
        $this->assertSame($this->resultRedirectMock, $this->action->execute());
    }

    /**
     * Testing that error message is added if error is occur
     */
    public function testExecuteErrorMessage()
    {
        $this->prepareCategoryMock();
        $this->categoryRepositoryMock->expects($this->any())
            ->method('get')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(__('Not found.'))
            );
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($this->equalTo('Not found.'));
        $this->action->execute();
    }

    /**
     * Testing that page config values is set
     */
    public function testExecutePageConfig()
    {
        $this->prepareCategoryMock();
        $this->titleMock->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->equalTo(self::CATEGORY_NAME));
        $this->pageConfigMock->expects($this->atLeastOnce())
            ->method('setMetadata')
            ->with(
                $this->equalTo('description'),
                $this->equalTo(self::CATEGORY_META_DESCRIPTION)
            );
        $this->action->execute();
    }

    /**
     * Testing of forwarding to noroute action if category is not valid
     *
     * @dataProvider executeForwardDataProvider
     */
    public function testExecuteForward($status, $storeId)
    {
        $this->prepareCategoryMock($status, $storeId);
        $this->forwardMock->expects($this->atLeastOnce())
            ->method('setModule')
            ->with($this->equalTo('cms'));
        $this->forwardMock->expects($this->atLeastOnce())
            ->method('setController')
            ->with($this->equalTo('noroute'));
        $this->forwardMock->expects($this->once())
            ->method('forward')
            ->with($this->equalTo('index'));
        $this->action->execute();
    }

    /**
     * Data provider for testExecuteForward method
     *
     * @return array
     */
    public function executeForwardDataProvider()
    {
        return [
            'category is disabled' => [0, null],
            'category from another store view' => [1, [2]]
        ];
    }
}
