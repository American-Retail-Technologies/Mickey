<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Test\Unit\Model;

use Aheadworks\Blog\Controller\Router;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\UrlInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\Data\TagInterface;
use Aheadworks\Blog\Model\Config;

/**
 * Test for \Aheadworks\Blog\Model\Url
 */
class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const ROUTE_TO_BLOG = 'blog';
    const POST_URL_KEY = 'post';
    const CATEGORY_URL_KEY = 'cat';
    const TAG_NAME = 'tag';
    /**#@-*/

    /**
     * @var Url
     */
    private $urlModel;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var PostInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postMock;

    /**
     * @var CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryMock;

    /**
     * @var TagInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getRouteToBlog'])
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->expects($this->any())->method('getRouteToBlog')
            ->will($this->returnValue(self::ROUTE_TO_BLOG));

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->postMock = $this->getMockBuilder(PostInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->categoryMock = $this->getMockBuilder(CategoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tagMock = $this->getMockBuilder(TagInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->urlModel = $objectManager->getObject(
            Url::class,
            [
                'config' => $configMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    /**
     * Testing that blog home url is built correctly
     */
    public function testGetBlogHomeUrlBuild()
    {
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                $this->equalTo(null),
                $this->equalTo(['_direct' => self::ROUTE_TO_BLOG . '/'])
            );
        $this->urlModel->getBlogHomeUrl();
    }

    /**
     * Testing return value of 'getBlogHomeUrl' method
     */
    public function testGetBlogHomeUrlResult()
    {
        $blogHomeUrl = 'http://localhost/blog';
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($blogHomeUrl);
        $this->assertEquals($blogHomeUrl, $this->urlModel->getBlogHomeUrl());
    }

    /**
     * Testing of 'getPostUrl' method
     */
    public function testGetPostUrl()
    {
        $blogPostUrl = 'http://localhost/blog/post';
        $this->postMock->expects($this->any())
            ->method('getUrlKey')
            ->willReturn(self::POST_URL_KEY);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($blogPostUrl);
        $this->assertEquals($blogPostUrl, $this->urlModel->getPostUrl($this->postMock));
    }

    /**
     * Testing return value of 'getPostRoute' method
     */
    public function testGetPostRouteResult()
    {
        $this->postMock->expects($this->any())
            ->method('getUrlKey')
            ->willReturn(self::POST_URL_KEY);
        $this->assertEquals(
            self::ROUTE_TO_BLOG . '/' . self::POST_URL_KEY . '/',
            $this->urlModel->getPostRoute($this->postMock)
        );
    }

    /**
     * Testing that category url is built correctly
     */
    public function testGetCategoryUrlBuild()
    {
        $this->categoryMock->expects($this->any())
            ->method('getUrlKey')
            ->willReturn(self::CATEGORY_URL_KEY);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                $this->equalTo(null),
                $this->equalTo(['_direct' => self::ROUTE_TO_BLOG . '/' . self::CATEGORY_URL_KEY . '/'])
            );
        $this->urlModel->getCategoryUrl($this->categoryMock);
    }

    /**
     * Testing return value of 'getCategoryUrl' method
     */
    public function testGetCategoryUrlResult()
    {
        $blogCategoryUrl = 'http://localhost/blog/cat';
        $this->categoryMock->expects($this->any())
            ->method('getUrlKey')
            ->willReturn(self::CATEGORY_URL_KEY);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($blogCategoryUrl);
        $this->assertEquals($blogCategoryUrl, $this->urlModel->getCategoryUrl($this->categoryMock));
    }

    /**
     * Testing return value of 'getCategoryRoute' method
     */
    public function testGetCategoryRouteResult()
    {
        $this->categoryMock->expects($this->any())
            ->method('getUrlKey')
            ->willReturn(self::CATEGORY_URL_KEY);
        $this->assertEquals(
            self::ROUTE_TO_BLOG . '/' . self::CATEGORY_URL_KEY . '/',
            $this->urlModel->getCategoryRoute($this->categoryMock)
        );
    }

    /**
     * Testing that search by tag url is built correctly
     */
    public function testGetSearchByTagUrlBuild()
    {
        $this->tagMock->expects($this->any())
            ->method('getName')
            ->willReturn(self::TAG_NAME);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                $this->equalTo(null),
                $this->equalTo(['_direct' => self::ROUTE_TO_BLOG . '/' . Router::TAG_KEY . '/' . self::TAG_NAME . '/'])
            );
        $this->urlModel->getSearchByTagUrl($this->tagMock);
    }

    /**
     * Testing that search by tag name url is built correctly
     */
    public function testGetSearchByTagNameUrlBuild()
    {
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                $this->equalTo(null),
                $this->equalTo(['_direct' => self::ROUTE_TO_BLOG . '/' . Router::TAG_KEY . '/' . self::TAG_NAME . '/'])
            );
        $this->urlModel->getSearchByTagUrl(self::TAG_NAME);
    }

    /**
     * Testing return value of 'getSearchByTagUrl' method
     */
    public function testGetSearchByTagResult()
    {
        $blogSearchByTagUrl = 'http://localhost/blog/tag/tag';
        $this->tagMock->expects($this->any())
            ->method('getName')
            ->willReturn(self::TAG_NAME);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($blogSearchByTagUrl);
        $this->assertEquals($blogSearchByTagUrl, $this->urlModel->getSearchByTagUrl($this->tagMock));
    }
}
