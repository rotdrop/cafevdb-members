<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @copyright Copyright (c) 2022 Claus-Justus Heine <himself@claus-justus-heine.de>
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\CAFeVDBMembers\Service;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ISession;
use Psr\Log\LoggerInterface;
use OCP\IL10N;

use OCA\CAFeVDBMembers\Exceptions;

class RequestService
{
  use \OCA\CAFeVDBMembers\Traits\LoggerTrait;

  const POST = 'post';
  const GET = 'get';
  const DELETE = 'delete';
  const PUT = 'put';

  const JSON = 'json';
  const URL_ENCODED = 'urlencoded';
  const DATA = 'data';

  /** @var IRequest */
  private $request;

  /** @var IURLGenerator */
  private $urlGenerator;

  /** @var ISession */
  private $session;

  /** @var IL10N */
  private $l;

  /**
   * @var bool
   *
   * Close the php-session if it is still open, just before actually
   * posting to a route on the same server. The default is to close
   * the session automatically if needed.
   */
  private $closeSession;

  public function __construct(
    IRequest $request
    , IURLGenerator $urlGenerator
    , ISession $session
    , LoggerInterface $logger
    , IL10N $l10n
    , bool $closeSession = true
  ) {
    $this->request = $request;
    $this->urlGenerator = $urlGenerator;
    $this->session = $session;
    $this->logger = $logger;
    $this->l = $l10n;
    $this->closeSession = $closeSession;
  }

  /**
   * Set the close-session-behaviour.
   *
   * @param bool $value If true close the session just before actually
   * calling out to a route on the same server.
   */
  public function setCloseSession(bool $value)
  {
    $this->closeSession = $value;
  }

  public function postToRoute(string $route,
                              array $routeParams = [],
                              array $requestData = [],
                              string $postType = self::JSON)
  {
    return $this->callInternalRoute($route, self::POST, $routeParams, $requestData, $postType);
  }

  public function getFromRoute(string $route,
                               array $routeParams = [],
                               array $requestData = [])
  {
    return $this->callInternalRoute($route, self::GET, $routeParams, $requestData);
  }

  /**
   * Post to a Cloud route.
   *
   * @param string $route Route name (i.e.: not the URL)
   *
   * @param array $routeParams Parameters built in to the URL (despite
   * the fact that we use POST)
   *
   * @param array $postData Stuff passed by the POST method.
   *
   * @param string $type How $postData is encoded. Can be 'json' or
   * 'urlencoded'. Default is 'json'.
   */
  public function callInternalRoute(string $route,
                                    string $method = self::POST,
                                    array $routeParams = [],
                                    array $requestData = [],
                                    string $postType = self::JSON)
  {
    if (!$this->session->isClosed()) {
      if ($this->closeSession) {
        $this->session->close();
      } else {
        throw new Exceptions\SessionStillOpenException(
          $this->l->t('Cannot call internal route while the session is open.'),
          session: $this->session
        );
      }
    }

    $this->logInfo('CALL ROUTE ' . $route . ' ' . print_r($routeParams, true) . ' ' . print_r($requestData, true));

    $headers = [];

    $requestToken = \OCP\Util::callRegister();
    $requestData['requesttoken'] = $requestToken;
    $urlParameters = [
      'requesttoken' => $requestToken,
      'format' => 'json',
    ];

    if ($method == self::GET) {
      $urlParameters = array_merge($urlParameters, $requestData);
      $requestData = null;
    }
    if (!empty($requestData)) {
      switch ($postType) {
        case self::JSON:
          if (is_array($requestData)) {
            $requestData = \OC_JSON::encode($requestData);
            $headers[] = 'Content-Type: application/json';
          }
          break;
        case self::URL_ENCODED:
          if (is_array($requestData)) {
            $requestData = http_build_query($requestData, '', '&');
          }
          break;
        default:
          throw new \InvalidArgumentException(
            $this->l->t('Supported data formats are "%1$s" and "%2$s", specified was "%3$s".', [
              self::JSON, self::URL_ENCODED, $postType
            ]));
          break;
      }
    }

    $url = $this->urlGenerator->linkToRouteAbsolute($route, array_merge($routeParams, $urlParameters));

    $cookies = array();
    foreach($this->request->cookies as $name => $value) {
      $cookies[] = "$name=" . urlencode($value);
    }

    $headers[] = 'OCS-APIRequest: true';
    $headers[] = 'Accept: application/json';
    $headers[] = 'requesttoken: ' . $requestToken;

    $c = curl_init($url);
    curl_setopt($c, CURLOPT_VERBOSE, 0);
    curl_setopt($c, CURLOPT_HEADER, 0);
    curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
    switch ($method) {
      case self::GET:
        curl_setopt($c, CURLOPT_HTTPGET, 1);
        curl_setopt($c, CURLOPT_POST, 0);
        break;
      case self::POST:
        curl_setopt($c, CURLOPT_POST, 1);
        break;
      case self::PUT:
        curl_setopt($c, CURLOPT_POST, 0);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PUT');
        break;
      case self::DELETE:
        curl_setopt($c, CURLOPT_POST, 0);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
        break;
    }
    if (!empty($requestData)) {
      curl_setopt($c, CURLOPT_POSTFIELDS, $requestData);
      $this->logDebug('CURL REQUEST DATA ' . $requestData);
    }
    if (count($cookies) > 0) {
      curl_setopt($c, CURLOPT_COOKIE, join("; ", $cookies));
    }
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    // this is internal, so there is no point in verifying certs:
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

    $result = curl_exec($c);
    curl_close($c);

    $responseData = json_decode($result, true);
    if (!is_array($responseData)) {
      throw new \RunTimeException(
        $this->l->t('Invalid response from API call: "%s"', print_r($result, true)));
    }

    $this->logDebug('RESPONSE DATA ' . print_r($responseData, true));

    // Some apps still return HTTP_STATUS_OK and code errors and success in
    // the old way instead of using HTTP-error-codes.
    if (($responseData['status']??null) != 'success' && isset($responseData['data'])) {
      throw new \RuntimeException(
        $this->l->t('Error response from call to internal route "%1$s": %2$s', [
          $route, $responseData['data']['message']??print_r($responseData, true)
        ]));
    }

    // parse OCS responses if format matches
    if (isset($responseData['ocs'])) {
      $meta = $responseData['ocs']['meta']??null;
      $data = $responseData['ocs']['data']??null;
      if ($meta === null || $data === null) {
        throw new \RuntimeException(
          $this->l->t('Invalid OCS response from call to internal route "%1$s": %2$s', [
            $route, $responseData,
          ]));
      }
      if ($meta['statuscode'] !== 100) {
        throw new \RuntimeException(
          $this->l->t('Error response from call to internal route "%1$s": %2$s --  %3$s', [
            $route, $meta['status']??'unknwown status', $meta['message']??print_r($meta, true)
          ]));
      }
      return $data;
    }

    if (isset($responseData['data'])) {
      return $responseData['data'];
    } else {
      return $responseData;
    }
  }
}
