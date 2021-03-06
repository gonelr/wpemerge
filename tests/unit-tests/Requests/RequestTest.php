<?php

namespace WPEmergeTests\Requests;

use WPEmerge\Requests\Request;
use WP_UnitTestCase;

/**
 * @coversDefaultClass \WPEmerge\Requests\Request
 */
class RequestTest extends WP_UnitTestCase {
	/**
	 * @covers ::fromGlobals
	 * @covers ::__construct
	 */
	public function testFromGlobals() {
		$expected_get = 'foo';
		$expected_post = 'bar';
		$expected_cookie = 'baz';
		$expected_files = 'foofoo';
		$expected_server = 'foobar';
		$key = 'test_key';

		$_GET[ $key ] = $expected_get;
		$_POST[ $key ] = $expected_post;
		$_COOKIE[ $key ] = $expected_cookie;
		$_FILES[ $key ] = $expected_files;
		$_SERVER[ $key ] = $expected_server;

		$subject = Request::fromGlobals();

		$this->assertEquals( $expected_get, $subject->get( $key ) );
		$this->assertEquals( $expected_post, $subject->post( $key ) );
		$this->assertEquals( $expected_cookie, $subject->cookie( $key ) );
		$this->assertEquals( $expected_files, $subject->files( $key ) );
		$this->assertEquals( $expected_server, $subject->server( $key ) );
	}

	/**
	 * @covers ::getMethod
	 */
	public function testGetMethod() {
		$expected1 = 'GET';
		$subject1 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected1, $subject1->getMethod() );

		$expected2 = 'POST';
		$subject2 = new Request( [], [], [], [], ['REQUEST_METHOD' => $expected2], [] );
		$this->assertEquals( $expected2, $subject2->getMethod() );

		$expected3 = 'PUT';
		$subject3 = new Request( [], [], [], [], ['REQUEST_METHOD' => 'POST'], ['X-HTTP-METHOD-OVERRIDE' => $expected3] );
		$this->assertEquals( $expected3, $subject3->getMethod() );

		$expected4 = 'PUT';
		$subject4 = new Request( [], ['_method' => 'PUT'], [], [], ['REQUEST_METHOD' => 'POST'], [] );
		$this->assertEquals( $expected4, $subject4->getMethod() );

		$expected5 = 'PUT';
		$subject5 = new Request( [], ['_method' => 'PUT'], [], [], ['REQUEST_METHOD' => 'POST'], ['X-HTTP-METHOD-OVERRIDE' => 'POST'] );
		$this->assertEquals( $expected5, $subject5->getMethod() );
	}

	/**
	 * @covers ::isGet
	 * @covers ::isHead
	 * @covers ::isPost
	 * @covers ::isPut
	 * @covers ::isPatch
	 * @covers ::isDelete
	 * @covers ::isOptions
	 */
	public function testIsMethod() {
		$subject1 = new Request( [], [], [], [], ['REQUEST_METHOD' => 'GET'], [] );
		$this->assertTrue( $subject1->isGet() );

		$subject = new Request( [], [], [], [], ['REQUEST_METHOD' => 'HEAD'], [] );
		$this->assertTrue( $subject->isHead() );

		$subject3 = new Request( [], [], [], [], ['REQUEST_METHOD' => 'POST'], [] );
		$this->assertTrue( $subject3->isPost() );

		$subject4 = new Request( [], [], [], [], ['REQUEST_METHOD' => 'PUT'], [] );
		$this->assertTrue( $subject4->isPut() );

		$subject5 = new Request( [], [], [], [], ['REQUEST_METHOD' => 'PATCH'], [] );
		$this->assertTrue( $subject5->isPatch() );

		$subject6 = new Request( [], [], [], [], ['REQUEST_METHOD' => 'DELETE'], [] );
		$this->assertTrue( $subject6->isDelete() );

		$subject7 = new Request( [], [], [], [], ['REQUEST_METHOD' => 'OPTIONS'], [] );
		$this->assertTrue( $subject7->isOptions() );
	}

	/**
	 * @covers ::isReadVerb
	 */
	public function testIsReadVerb() {
		$subject1 = new Request( [], [], [], [], ['REQUEST_METHOD' => 'GET'], [] );
		$this->assertTrue( $subject1->isReadVerb() );

		$subject2 = new Request( [], [], [], [], ['REQUEST_METHOD' => 'HEAD'], [] );
		$this->assertTrue( $subject2->isReadVerb() );

		$subject3 = new Request( [], [], [], [], ['REQUEST_METHOD' => 'OPTIONS'], [] );
		$this->assertTrue( $subject3->isReadVerb() );
	}

	/**
	 * @covers ::getUrl
	 */
	public function testGetUrl() {
		$expected = 'http://example.com/';
		$subject = new Request( [], [], [], [], [
			'HTTP_HOST' => 'example.com',
			'REQUEST_URI' => '/',
		], [] );
		$this->assertEquals( $expected, $subject->getUrl() );
	}

	/**
	 * @covers ::getUrl
	 */
	public function testGetUrl_Https_Https() {
		$expected = 'https://example.com/';
		$subject = new Request( [], [], [], [], [
			'HTTPS' => 'on',
			'HTTP_HOST' => 'example.com',
			'REQUEST_URI' => '/',
		], [] );
		$this->assertEquals( $expected, $subject->getUrl() );
	}

	/**
	 * @covers ::getUrl
	 */
	public function testGetUrl_UriWithoutSlash_AddsLeadingSlashToUri() {
		$expected = 'http://example.com/foo/bar';
		$subject = new Request( [], [], [], [], [
			'HTTP_HOST' => 'example.com',
			'REQUEST_URI' => 'foo/bar',
		], [] );
		$this->assertEquals( $expected, $subject->getUrl() );
	}

	/**
	 * @covers ::get
	 * @covers ::post
	 * @covers ::cookie
	 * @covers ::files
	 * @covers ::server
	 * @covers ::headers
	 * @covers ::input
	 */
	public function testGet_ExistingKey_ReturnValue() {
		$expected = 'foo';
		$key = 'key';
		$values = [$key => $expected];

		$subject1 = new Request( $values, [], [], [], [], [] );
		$this->assertEquals( $expected, $subject1->get( $key ) );

		$subject2 = new Request( [], $values, [], [], [], [] );
		$this->assertEquals( $expected, $subject2->post( $key ) );

		$subject3 = new Request( [], [], $values, [], [], [] );
		$this->assertEquals( $expected, $subject3->cookie( $key ) );

		$subject4 = new Request( [], [], [], $values, [], [] );
		$this->assertEquals( $expected, $subject4->files( $key ) );

		$subject5 = new Request( [], [], [], [], $values, [] );
		$this->assertEquals( $expected, $subject5->server( $key ) );

		$subject6 = new Request( [], [], [], [], [], $values );
		$this->assertEquals( $expected, $subject6->headers( $key ) );
	}

	/**
	 * @covers ::get
	 * @covers ::post
	 * @covers ::cookie
	 * @covers ::files
	 * @covers ::server
	 * @covers ::headers
	 * @covers ::input
	 */
	public function testGet_NonExistantKey_ReturnNull() {
		$expected = null;
		$key = 'key';

		$subject1 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject1->get( $key ) );

		$subject2 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject2->post( $key ) );

		$subject3 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject3->cookie( $key ) );

		$subject4 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject4->files( $key ) );

		$subject5 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject5->server( $key ) );

		$subject6 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject6->headers( $key ) );
	}

	/**
	 * @covers ::get
	 * @covers ::post
	 * @covers ::cookie
	 * @covers ::files
	 * @covers ::server
	 * @covers ::headers
	 * @covers ::input
	 */
	public function testGet_NonExistantKeyWithDefault_ReturnDefault() {
		$expected = 'foo';
		$key = 'key';

		$subject1 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject1->get( $key, $expected ) );

		$subject2 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject2->post( $key, $expected ) );

		$subject3 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject3->cookie( $key, $expected ) );

		$subject4 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject4->files( $key, $expected ) );

		$subject5 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject5->server( $key, $expected ) );

		$subject6 = new Request( [], [], [], [], [], [] );
		$this->assertEquals( $expected, $subject6->headers( $key, $expected ) );
	}

	/**
	 * @covers ::get
	 * @covers ::post
	 * @covers ::cookie
	 * @covers ::files
	 * @covers ::server
	 * @covers ::headers
	 * @covers ::input
	 */
	public function testGet_NoKey_ReturnAll() {
		$expected = ['foo' => 'bar'];

		$subject1 = new Request( $expected, [], [], [], [], [] );
		$this->assertEquals( $expected, $subject1->get() );

		$subject2 = new Request( [], $expected, [], [], [], [] );
		$this->assertEquals( $expected, $subject2->post() );

		$subject3 = new Request( [], [], $expected, [], [], [] );
		$this->assertEquals( $expected, $subject3->cookie() );

		$subject4 = new Request( [], [], [], $expected, [], [] );
		$this->assertEquals( $expected, $subject4->files() );

		$subject5 = new Request( [], [], [], [], $expected, [] );
		$this->assertEquals( $expected, $subject5->server() );

		$subject6 = new Request( [], [], [], [], [], $expected );
		$this->assertEquals( $expected, $subject6->headers() );
	}
}
