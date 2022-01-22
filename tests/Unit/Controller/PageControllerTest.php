<?php

namespace OCA\CAFeVDBMembers\Controller;

use PHPUnit\Framework\TestCase;

use OCP\AppFramework\Http\TemplateResponse;

class PageControllerTest extends TestCase {
  private $controller;
  private $appName;

  public function setUp(): void {
    $infoXml = new \SimpleXMLElement(file_get_contents(__DIR__ . '/../../../appinfo/info.xml'));
    $this->appName = (string)$infoXml->id;
    $request = $this->getMockBuilder('OCP\IRequest')->getMock();
    $this->controller = new PageController($this->appName, $request);
  }


  public function testIndex() {
    $result = $this->controller->index();

    $this->assertEquals('main', $result->getTemplateName());
    $this->assertTrue($result instanceof TemplateResponse);
  }
}
