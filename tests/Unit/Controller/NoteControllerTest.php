<?php

namespace OCA\CAFeVDBMembers\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

use OCP\AppFramework\Http;
use OCP\IRequest;

use OCA\CAFeVDBMembers\Service\NoteNotFound;
use OCA\CAFeVDBMembers\Service\NoteService;
use OCA\CAFeVDBMembers\Controller\NoteController;

class NoteControllerTest extends TestCase {
  protected $appName;
  protected $controller;
  protected $service;
  protected $userId = 'john';
  protected $request;

  public function setUp(): void {
    $infoXml = new \SimpleXMLElement(file_get_contents(__DIR__ . '/../../../appinfo/info.xml'));
    $this->appName = (string)$infoXml->id;

    $this->request = $this->getMockBuilder(IRequest::class)->getMock();
    $this->service = $this->getMockBuilder(NoteService::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->controller = new NoteController($this->appName, $this->request, $this->service, $this->userId);
  }

  public function testUpdate() {
    $note = 'just check if this value is returned correctly';
    $this->service->expects($this->once())
      ->method('update')
      ->with($this->equalTo(3),
          $this->equalTo('title'),
          $this->equalTo('content'),
           $this->equalTo($this->userId))
      ->will($this->returnValue($note));

    $result = $this->controller->update(3, 'title', 'content');

    $this->assertEquals($note, $result->getData());
  }


  public function testUpdateNotFound() {
    // test the correct status code if no note is found
    $this->service->expects($this->once())
      ->method('update')
      ->will($this->throwException(new NoteNotFound()));

    $result = $this->controller->update(3, 'title', 'content');

    $this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
  }
}
