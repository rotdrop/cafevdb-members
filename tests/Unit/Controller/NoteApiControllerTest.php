<?php

namespace OCA\CAFeVDBMembers\Tests\Unit\Controller;

use OCA\CAFeVDBMembers\Controller\NoteApiController;

class NoteApiControllerTest extends NoteControllerTest {
  public function setUp(): void {
    parent::setUp();
    $this->controller = new NoteApiController($this->request, $this->service, $this->userId);
  }
}
