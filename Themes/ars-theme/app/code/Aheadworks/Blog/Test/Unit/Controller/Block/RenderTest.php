<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Test\Unit\Controller\Block;

use Aheadworks\Blog\Block\Widget\RecentPost;
use Aheadworks\Blog\Controller\Block\Render;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\View;
use Magento\Framework\View\Layout;

/**
 * Test for \Aheadworks\Blog\Controller\Block\Render
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Render
     */
    private $controller;

    /**
     * @var InlineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translateInlineMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var View|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->translateInlineMock = $this->getMockForAbstractClass(InlineInterface::class);
        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['isAjax']
        );
        $this->responseMock = $this->getMockForAbstractClass(
            ResponseInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['appendBody']
        );
        $this->viewMock = $this->getMockBuilder(View::class)
            ->setMethods(['loadLayout', 'getLayout'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $objectManager->getObject(
            Context::class,
            [
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'view' => $this->viewMock
            ]
        );

        $this->controller = $objectManager->getObject(
            Render::class,
            [
                'context' => $contextMock,
                'translateInline' => $this->translateInlineMock
            ]
        );
    }

    /**
     * Testing of execute method, if is not ajax request
     */
    public function testExecuteIsNotAjax()
    {
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(false);
        $resultRedirectMock = $this->getMockBuilder(ResultRedirect::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectMock->expects($this->once())
            ->method('setRefererOrBaseUrl')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Testing of execute method for blocks, if is ajax request
     */
    public function testExecuteBlockIsAjax()
    {
        $blockName = RecentPost::WIDGET_NAME_PREFIX . 'name';
        $block = base64_encode(
            serialize(
                [
                    'name' => $blockName,
                    'number_to_display' => 'number_to_display',
                    'title' => 'title',
                    'template' => 'template'
                ]
            )
        );
        $blocks = [$block];
        $expected = [$block => 'html content'];

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['blocks', null, json_encode($blocks)]
                ]
            );

        $blockInstanceMock = $this->getMockBuilder(RecentPost::class)
            ->setMethods(['toHtml', 'setNameInLayout'])
            ->disableOriginalConstructor()
            ->getMock();
        $blockInstanceMock->expects($this->once())
            ->method('setNameInLayout')
            ->with($blockName);
        $blockInstanceMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($expected[$block]);
        $layoutMock = $this->getMockBuilder(Layout::class)
            ->setMethods(['createBlock'])
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(RecentPost::class)
            ->willReturn($blockInstanceMock);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->translateInlineMock->expects($this->once())
            ->method('processResponseBody')
            ->with($expected)
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with(json_encode($expected));

        $this->controller->execute();
    }
}
