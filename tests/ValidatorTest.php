<?php
namespace vakata\validation\test;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass() {
	}
	public static function tearDownAfterClass() {
	}
	protected function setUp() {
	}
	protected function tearDown() {
	}

	protected function map(array $errors) {
		$rtrn = [];
		foreach ($errors as $error) {
			if (isset($error['message'])) {
				$rtrn[] = $error['message'];
			}
		}
		return implode(',', $rtrn);
	}

	public function testOneKey() {
		$v = new \vakata\validation\Validator();
		$v->numeric("numeric");
		$this->assertEquals([['key'=>'','message'=>'numeric','value'=>'not-numeric']], $v->run("not-numeric"));
		$this->assertEquals([], $v->run("1"));
	}
	public function testRequired() {
		$v = new \vakata\validation\Validator();
		$v->required("req", "required");
		$this->assertEquals("required", $this->map($v->run(null)));
		$this->assertEquals("required", $this->map($v->run("non-array")));
		$this->assertEquals("required", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("", $this->map($v->run(["req"=>''])));
		$this->assertEquals("", $this->map($v->run(["req"=>'1'])));
		$this->assertEquals("", $this->map($v->run(["req"=>'1', "extra"=>'1'])));
	}
	public function testRequiredArray() {
		$v = new \vakata\validation\Validator();
		$v->required("req1", "required1")->required("req2", "required2");
		$this->assertEquals("required1,required2", $this->map($v->run(null)));
		$this->assertEquals("required1,required2", $this->map($v->run("non-array")));
		$this->assertEquals("required1,required2", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("required2", $this->map($v->run(["req1"=>''])));
		$this->assertEquals("required1", $this->map($v->run(["req2"=>''])));
		$this->assertEquals("", $this->map($v->run(["req1"=>'1',"req2"=>''])));
	}
	public function testRequiredChain() {
		$v = new \vakata\validation\Validator();
		$v->required("req", "required")->numeric("numeric");
		$this->assertEquals("required,numeric", $this->map($v->run(null)));
		$this->assertEquals("required,numeric", $this->map($v->run("non-array")));
		$this->assertEquals("required,numeric", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("numeric", $this->map($v->run(["req"=>''])));
		$this->assertEquals("", $this->map($v->run(["req"=>'2'])));
	}
	public function testRequiredOptionalArray() {
		$v = new \vakata\validation\Validator();
		$v->required("req1", "required")->optional("req2")->numeric("numeric");
		$this->assertEquals("required", $this->map($v->run(null)));
		$this->assertEquals("required", $this->map($v->run("non-array")));
		$this->assertEquals("required", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("required,numeric", $this->map($v->run(["req2"=>''])));
		$this->assertEquals("numeric", $this->map($v->run(["req1"=>'1',"req2"=>''])));
		$this->assertEquals("", $this->map($v->run(["req1"=>''])));
		$this->assertEquals("", $this->map($v->run(["req1"=>'1',"req2"=>'1'])));
	}
	public function testRequiredNestedArray() {
		$v = new \vakata\validation\Validator();
		$v->required('test.nested', 'required')->numeric('numeric');
		$this->assertEquals("required,numeric", $this->map($v->run(null)));
		$this->assertEquals("required,numeric", $this->map($v->run("non-array")));
		$this->assertEquals("required,numeric", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>''])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>[]])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>['test']])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>['nested'=>'']])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>['nested'=>[]]])));
		$this->assertEquals("", $this->map($v->run(["test"=>['nested'=>'1']])));
	}
	public function testOptionalNestedArray() {
		$v = new \vakata\validation\Validator();
		$v->optional('test.nested')->numeric('numeric');
		$this->assertEquals("", $this->map($v->run(null)));
		$this->assertEquals("", $this->map($v->run("non-array")));
		$this->assertEquals("", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("", $this->map($v->run(["test"=>''])));
		$this->assertEquals("", $this->map($v->run(["test"=>[]])));
		$this->assertEquals("", $this->map($v->run(["test"=>['test']])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>['nested'=>'']])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>['nested'=>[]]])));
		$this->assertEquals("", $this->map($v->run(["test"=>['nested'=>'1']])));
	}
	public function testRequiredWildcardArray() {
		$v = new \vakata\validation\Validator();
		$v->required('test.*', "required")->numeric("numeric");

		$this->assertEquals("required,numeric", $this->map($v->run(null)));
		$this->assertEquals("required,numeric", $this->map($v->run("non-array")));
		$this->assertEquals("required,numeric", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>''])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>[]])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>['asdf']])));
		$this->assertEquals("numeric,numeric", $this->map($v->run(["test"=>['asdf','asdf']])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>['asdf',1]])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>[1,'asdf',1]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[1,1]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[1]])));
	}
	public function testOptionalWildcardArray() {
		$v = new \vakata\validation\Validator();
		$v->optional('test.*')->numeric("numeric");

		$this->assertEquals("", $this->map($v->run(null)));
		$this->assertEquals("", $this->map($v->run("non-array")));
		$this->assertEquals("", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("", $this->map($v->run(["test"=>''])));
		$this->assertEquals("", $this->map($v->run(["test"=>[]])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>['asdf']])));
		$this->assertEquals("numeric,numeric", $this->map($v->run(["test"=>['asdf','asdf']])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>['asdf',1]])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>[1,'asdf',1]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[1,1]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[1]])));
	}
	public function testRequiredNestedWildcardArray() {
		$v = new \vakata\validation\Validator();
		$v->required('test.*.id', "required")->numeric("numeric");

		$this->assertEquals("required,numeric", $this->map($v->run(null)));
		$this->assertEquals("required,numeric", $this->map($v->run("non-array")));
		$this->assertEquals("required,numeric", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>''])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>[]])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>['asdf']])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>'']]])));
		$this->assertEquals("numeric,numeric", $this->map($v->run(["test"=>[['id'=>''], ['id'=>'asdf']]])));
		$this->assertEquals("required,required,numeric,numeric", $this->map($v->run(["test"=>[['id'=>null], []]])));
		$this->assertEquals("required,numeric,numeric", $this->map($v->run(["test"=>[['id'=>''], []]])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>1], ['id'=>'asdf']]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>1], ['id'=>2]]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>1]]])));
	}
	public function testOptionalNestedWildcardArray() {
		$v = new \vakata\validation\Validator();
		$v->optional('test.*.id')->numeric("numeric");

		$this->assertEquals("", $this->map($v->run(null)));
		$this->assertEquals("", $this->map($v->run("non-array")));
		$this->assertEquals("", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("", $this->map($v->run(["test"=>''])));
		$this->assertEquals("", $this->map($v->run(["test"=>[]])));
		$this->assertEquals("", $this->map($v->run(["test"=>['asdf']])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>'']]])));
		$this->assertEquals("numeric,numeric", $this->map($v->run(["test"=>[['id'=>''], ['id'=>'asdf']]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>null], []]])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>''], []]])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>1], ['id'=>'asdf']]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>1], ['id'=>2]]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>1]]])));
	}
	public function testRequiredNestedMultiWildcardArray() {
		$v = new \vakata\validation\Validator();
		$v->required('test.*.id.*', "required")->numeric("numeric");

		$this->assertEquals("required,numeric", $this->map($v->run(null)));
		$this->assertEquals("required,numeric", $this->map($v->run("non-array")));
		$this->assertEquals("required,numeric", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>''])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>[]])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>['asdf']])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>[['id'=>'']]])));
		$this->assertEquals("numeric,numeric", $this->map($v->run(["test"=>[['id'=>['']], ['id'=>['asdf']]]])));
		$this->assertEquals("required,required,numeric,numeric", $this->map($v->run(["test"=>[['id'=>[]], []]])));
		$this->assertEquals("required,numeric", $this->map($v->run(["test"=>[['id'=>[1]], []]])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>[1]], ['id'=>['asdf']]]])));
		$this->assertEquals("numeric,numeric,numeric", $this->map($v->run(["test"=>[['id'=>['',1,'b']], ['id'=>['a',2]]]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>[1]], ['id'=>[2]]]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>[1]]]])));
	}
	public function testOptionalNestedMultiWildcardArray() {
		$v = new \vakata\validation\Validator();
		$v->optional('test.*.id.*')->numeric("numeric");

		$this->assertEquals("", $this->map($v->run(null)));
		$this->assertEquals("", $this->map($v->run("non-array")));
		$this->assertEquals("", $this->map($v->run(["wrong"=>''])));
		$this->assertEquals("", $this->map($v->run(["test"=>''])));
		$this->assertEquals("", $this->map($v->run(["test"=>[]])));
		$this->assertEquals("", $this->map($v->run(["test"=>['asdf']])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>'']]])));
		$this->assertEquals("numeric,numeric", $this->map($v->run(["test"=>[['id'=>['']], ['id'=>['asdf']]]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>[]], []]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>[1]], []]])));
		$this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>[1]], ['id'=>['asdf']]]])));
		$this->assertEquals("numeric,numeric,numeric", $this->map($v->run(["test"=>[['id'=>['',1,'b']], ['id'=>['a',2]]]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>[1]], ['id'=>[2]]]])));
		$this->assertEquals("", $this->map($v->run(["test"=>[['id'=>[1,2,3]]]])));
	}
	public function testMultiRules() {
		$v = new \vakata\validation\Validator();
		$v
			->required('name', 'requiredN')->alpha(null, "alphaN")->notEmpty("empty")
			->required('family', 'requiredF')->alpha(null, "alphaF")
			->required('age', 'requiredA')->numeric("numericA")
			->optional("newsletter")->numeric("numericN")
			->optional("children.*.name")->alpha(null, "alphaC")
			->optional("children.*.age")->numeric(null, "numericC");

		$this->assertEquals("requiredN,empty,requiredF,requiredA,numericA", $this->map($v->run(null)));
		$this->assertEquals(
			"empty,requiredF,requiredA,numericA",
			$this->map($v->run([
				'name' => ''
			]))
		);
		$this->assertEquals(
			"alphaN,requiredF,requiredA,numericA",
			$this->map($v->run([
				'name' => 'g1'
			]))
		);
		$this->assertEquals(
			"requiredA,numericA",
			$this->map($v->run([
				'name' => 'Ivan',
				'family' => 'Bozhanov'
			]))
		);
		$this->assertEquals(
			"",
			$this->map($v->run([
				'name' => 'Ivan',
				'family' => 'Bozhanov',
				'age' => '32'
			]))
		);
		$this->assertEquals(
			"numericN",
			$this->map($v->run([
				'name' => 'Ivan',
				'family' => 'Bozhanov',
				'age' => '32',
				'newsletter' => 'asdf'
			]))
		);
		$this->assertEquals(
			"alphaC",
			$this->map($v->run([
				'name' => 'Ivan',
				'family' => 'Bozhanov',
				'age' => '32',
				'newsletter' => '1',
				'children' => [
					[ 'name' => '1' ]
				]
			]))
		);
		$this->assertEquals(
			"",
			$this->map($v->run([
				'name' => 'Ivan',
				'family' => 'Bozhanov',
				'age' => '32',
				'newsletter' => '1',
				'children' => [
					[ 'name' => 'a', 'age' => 1 ],
					[ 'name' => 'b', 'age' => 2 ]
				]
			]))
		);
	}

	// multiple rules and keys
	// each testing function
}
