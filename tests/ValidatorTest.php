<?php
namespace vakata\validation\test;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    protected function map(array $errors) {
        $rtrn = [];
        foreach ($errors as $error) {
            if (is_array($error) && isset($error['message'])) {
                $rtrn[] = $error['message'];
            }
        }
        return implode(',', $rtrn);
    }

    public function testOneKey() {
        $v = new \vakata\validation\Validator();
        $v->numeric("numeric");
        $this->assertEquals([['key'=>'','message'=>'numeric','value'=>'not-numeric','rule'=>'numeric','data'=>[]]], $v->run("not-numeric"));
        $this->assertEquals([], $v->run("1"));
    }
    public function testRequired() {
        $v = new \vakata\validation\Validator();
        $v->required("req", "required");
        $this->assertEquals("required", $this->map($v->run(null)));
        $this->assertEquals("required", $this->map($v->run("non-array")));
        $this->assertEquals("required", $this->map($v->run(["wrong"=>''])));
        $this->assertEquals("required", $this->map($v->run(["req"=>''])));
        $this->assertEquals("", $this->map($v->run(["req"=>'1'])));
        $this->assertEquals("", $this->map($v->run(["req"=>'1', "extra"=>'1'])));
    }
    public function testRequiredArray() {
        $v = new \vakata\validation\Validator();
        $v->required("req1", "required1")->required("req2", "required2");
        $this->assertEquals("required1,required2", $this->map($v->run(null)));
        $this->assertEquals("required1,required2", $this->map($v->run("non-array")));
        $this->assertEquals("required1,required2", $this->map($v->run(["wrong"=>''])));
        $this->assertEquals("required1,required2", $this->map($v->run(["req1"=>''])));
        $this->assertEquals("required1,required2", $this->map($v->run(["req2"=>''])));
        $this->assertEquals("required2", $this->map($v->run(["req1"=>'1',"req2"=>''])));
        $this->assertEquals("required1", $this->map($v->run(["req1"=>'',"req2"=>'1'])));
        $this->assertEquals("", $this->map($v->run(["req1"=>'1',"req2"=>'1'])));
    }
    public function testDisabled() {
        $v = new \vakata\validation\Validator();
        $v->required("req1", "required1")->required("req2", "required2");
        $v->rules('req2')[0]->disable();
        $this->assertEquals("required1", $this->map($v->run(null)));
        $this->assertEquals("required1", $this->map($v->run("non-array")));
        $this->assertEquals("required1", $this->map($v->run(["wrong"=>''])));
        $this->assertEquals("required1", $this->map($v->run(["req1"=>''])));
        $this->assertEquals("required1", $this->map($v->run(["req2"=>''])));
        $this->assertEquals("", $this->map($v->run(["req1"=>'1',"req2"=>''])));
        $this->assertEquals("required1", $this->map($v->run(["req1"=>'',"req2"=>'1'])));
        $this->assertEquals("", $this->map($v->run(["req1"=>'1',"req2"=>'1'])));
    }
    public function testRequiredChain() {
        $v = new \vakata\validation\Validator();
        $v->required("req", "required")->numeric("numeric");
        $this->assertEquals("required,numeric", $this->map($v->run(null)));
        $this->assertEquals("required,numeric", $this->map($v->run("non-array")));
        $this->assertEquals("required,numeric", $this->map($v->run(["wrong"=>''])));
        $this->assertEquals("required,numeric", $this->map($v->run(["req"=>''])));
        $this->assertEquals("numeric", $this->map($v->run(["req"=>'a'])));
        $this->assertEquals("", $this->map($v->run(["req"=>'2'])));
    }
    public function testRequiredOptionalArray() {
        $v = new \vakata\validation\Validator();
        $v->required("req1", "required")->optional("req2")->numeric("numeric");
        $this->assertEquals("required", $this->map($v->run(null)));
        $this->assertEquals("required", $this->map($v->run("non-array")));
        $this->assertEquals("required", $this->map($v->run(["wrong"=>''])));
        $this->assertEquals("required", $this->map($v->run(["req2"=>''])));
        $this->assertEquals("", $this->map($v->run(["req1"=>'1',"req2"=>''])));
        $this->assertEquals("required", $this->map($v->run(["req1"=>''])));
        $this->assertEquals("", $this->map($v->run(["req1"=>'1'])));
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
        $this->assertEquals("required,numeric", $this->map($v->run(["test"=>['nested'=>'']])));
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
        $this->assertEquals("", $this->map($v->run(["test"=>['nested'=>'']])));
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
        $this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>'a']]])));
        $this->assertEquals("required,numeric,numeric", $this->map($v->run(["test"=>[['id'=>''], ['id'=>'asdf']]])));
        $this->assertEquals("required,required,numeric,numeric", $this->map($v->run(["test"=>[['id'=>null], []]])));
        $this->assertEquals("required,numeric,numeric", $this->map($v->run(["test"=>[['id'=>'a'], []]])));
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
        $this->assertEquals("", $this->map($v->run(["test"=>[['id'=>'']]])));
        $this->assertEquals("numeric,numeric", $this->map($v->run(["test"=>[['id'=>'asdf'], ['id'=>'asdf']]])));
        $this->assertEquals("", $this->map($v->run(["test"=>[['id'=>null], []]])));
        $this->assertEquals("", $this->map($v->run(["test"=>[['id'=>''], []]])));
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
        $this->assertEquals("numeric,numeric", $this->map($v->run(["test"=>[['id'=>['asdf']], ['id'=>['asdf']]]])));
        $this->assertEquals("required,required,numeric,numeric", $this->map($v->run(["test"=>[['id'=>[]], []]])));
        $this->assertEquals("required,numeric", $this->map($v->run(["test"=>[['id'=>[1]], []]])));
        $this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>[1]], ['id'=>['asdf']]]])));
        $this->assertEquals("required,numeric,numeric,numeric", $this->map($v->run(["test"=>[['id'=>['',1,'b']], ['id'=>['a',2]]]])));
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
        $this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>['']], ['id'=>['asdf']]]])));
        $this->assertEquals("", $this->map($v->run(["test"=>[['id'=>[]], []]])));
        $this->assertEquals("", $this->map($v->run(["test"=>[['id'=>[1]], []]])));
        $this->assertEquals("numeric", $this->map($v->run(["test"=>[['id'=>[1]], ['id'=>['asdf']]]])));
        $this->assertEquals("numeric,numeric,numeric", $this->map($v->run(["test"=>[['id'=>['v',1,'b']], ['id'=>['a',2]]]])));
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
            ->optional("children.*.age")->numeric("numericC");

        $this->assertEquals("requiredN,empty,requiredF,requiredA,numericA", $this->map($v->run(null)));
        $this->assertEquals(
            "requiredN,empty,requiredF,requiredA,numericA",
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
    public function testCallback() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->callback(function ($value, $data) {
            if (isset($data['other'])) {
                return false;
            }
            return $value === '1234';
        }, 'cb1');
        $this->assertEquals("", $this->map($v->run(['key' => '1234'])));
        $this->assertEquals("cb1", $this->map($v->run(['key' => '12345'])));
        $this->assertEquals("cb1", $this->map($v->run(['key' => '1234', 'other'=>'1234'])));
    }
    public function testRegex() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->regex('(^[a-z]{2}$)', 'err');
        $this->assertEquals("", $this->map($v->run(['key' => 'aa'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1a'])));
    }
    public function testNotRegex() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->notRegex('([a-z])', 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '1'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'a'])));
    }
    public function testNumeric() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->numeric('err');
        $this->assertEquals("", $this->map($v->run(['key' => '12'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1a'])));
    }
    public function testNotNumeric() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->notNumeric('err');
        $this->assertEquals("", $this->map($v->run(['key' => 'a'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'a1a'])));
    }
    public function testChars() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->chars('abc', 'err');
        $this->assertEquals("", $this->map($v->run(['key' => 'aabbbcaaa'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'a1'])));
    }
    public function testNotChars() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->notChars('abc', 'err');
        $this->assertEquals("", $this->map($v->run(['key' => 'ghj'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'gha'])));
    }
    public function testLatin() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->latin(false, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => 'aabbbcaaa'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'a1'])));
    }
    public function testNotLatin() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->notLatin('err');
        $this->assertEquals("", $this->map($v->run(['key' => '1'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'a'])));
    }
    public function testAlpha() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->alpha(false, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => 'Ivan'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'Иван'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'a1'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'Иван Божанов'])));
        $v->optional('key2')->alpha(true, 'err');
        $this->assertEquals("", $this->map($v->run(['key2' => 'Иван Божанов'])));
    }
    public function testUpper() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->upper(false, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => 'IVAN'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'ИВАН'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'Иван'])));
    }
    public function testLower() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->lower(false, 'err');
        $this->assertEquals("err", $this->map($v->run(['key' => 'IVAN'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'Иван'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'ivan'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'иван'])));
    }
    public function testAlphanumeric() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->alphanumeric(false, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => 'aabbbcaaa'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'aabbbc2aaa'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'a1-'])));
    }
    public function testNotEmpty() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->notEmpty('err');
        $this->assertEquals("", $this->map($v->run(['key' => 'aabbbcaaa'])));
        $this->assertEquals("", $this->map($v->run(['key' => ' '])));
        $this->assertEquals("", $this->map($v->run(['key' => ''])));
    }
    public function testMail() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->mail('err');
        $this->assertEquals("", $this->map($v->run(['key' => 'test@test.museum'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'test@'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'test.com'])));
    }
    public function testFloat() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->float('err');
        $this->assertEquals("", $this->map($v->run(['key' => '2.1'])));
        $this->assertEquals("", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '2,1'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'asdf'])));
        $this->assertEquals("", $this->map($v->run(['key' => ''])));
    }
    public function testInt() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->int('err');
        $this->assertEquals("err", $this->map($v->run(['key' => '2.1'])));
        $this->assertEquals("", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '2,1'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'asdf'])));
        $this->assertEquals("", $this->map($v->run(['key' => ''])));
    }
    public function testMin() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->min(2, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("", $this->map($v->run(['key' => '3'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '0'])));
    }
    public function testMax() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->max(3, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("", $this->map($v->run(['key' => '3'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '4'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '5'])));
    }
    public function testBetween() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->between(3, 5, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '3'])));
        $this->assertEquals("", $this->map($v->run(['key' => '4'])));
        $this->assertEquals("", $this->map($v->run(['key' => '5'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '6'])));
    }
    public function testEquals() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->equals(3, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '3'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '4'])));
    }
    public function testLength() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->length(3, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '123'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'asd'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'асд'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("", $this->map($v->run(['key' => ''])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'асдф'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'asdf'])));
    }
    public function testMinLength() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->minLength(3, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '123'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'asd'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'асд'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("", $this->map($v->run(['key' => ''])));
        $this->assertEquals("", $this->map($v->run(['key' => 'асдф'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'asdf'])));
    }
    public function testMaxLength() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->maxLength(3, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '123'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'asd'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'асд'])));
        $this->assertEquals("", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("", $this->map($v->run(['key' => ''])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'асдф'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'asdf'])));
    }
    public function testInArray() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->inArray([1,2,3], 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '1'])));
        $this->assertEquals("", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("", $this->map($v->run(['key' => '3'])));
        $this->assertEquals("", $this->map($v->run(['key' => ''])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'asdf'])));
    }
    public function testNotInArray() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->notInArray([1,2,3], 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '4'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1'])));
    }
    public function testDate() {
        $v = new \vakata\validation\Validator();

        $v->optional('ts')->date(null, 'err1');
        $this->assertEquals("", $this->map($v->run(['ts' => time()])));
        $this->assertEquals("", $this->map($v->run(['ts' => '01.02.2016'])));
        $this->assertEquals("err1", $this->map($v->run(['ts' => 's/2016-1 asdf'])));

        $v->optional('date1')->date('d.m.Y', 'err2');
        $this->assertEquals("err2", $this->map($v->run(['date1' => time()])));
        $this->assertEquals("", $this->map($v->run(['date1' => '01.02.2016'])));
        $this->assertEquals("err2", $this->map($v->run(['date1' => 's/2016-1 asdf'])));
        $this->assertEquals("err2", $this->map($v->run(['date1' => '12/12/2016'])));
        $this->assertEquals("err2", $this->map($v->run(['date1' => '43.01.2016'])));
    }
    public function testMinDate() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->minDate('12.12.2014', 'd.m.Y', 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '12.12.2014'])));
        $this->assertEquals("", $this->map($v->run(['key' => '15.12.2016'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 0])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.11.2011'])));
    }
    public function testMaxDate() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->maxDate('12.12.2014', 'd.m.Y', 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '12.12.2014'])));
        $this->assertEquals("", $this->map($v->run(['key' => '11.12.2014'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.11.2017'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.11.2022'])));
    }
    public function testBetweenDate() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->betweenDate('10.12.2014', '12.12.2014', 'd.m.Y', 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '12.12.2014'])));
        $this->assertEquals("", $this->map($v->run(['key' => '11.12.2014'])));
        $this->assertEquals("", $this->map($v->run(['key' => '10.12.2014'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.11.2017'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '09.12.2014'])));
    }
    public function testAge() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->age(18, null, 'd.m.Y', 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '12.12.1970'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.12.'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '10.12.2014'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.11.2017'])));
    }
    public function testAge2() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->age(18, '01.01.2018', 'd.m.Y', 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '12.12.1970'])));
        $this->assertEquals("", $this->map($v->run(['key' => '01.01.2000'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.12.'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '10.12.2014'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.11.2017'])));
    }
    public function testJson() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->json('err');
        $this->assertEquals("", $this->map($v->run(['key' => json_encode('12.12.1970')])));
        $this->assertEquals("", $this->map($v->run(['key' => json_encode(['a' => '01.01.2000'])])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.12.'])));
        $this->assertEquals("", $this->map($v->run(['key' => ''])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.11.2017'])));
    }
    public function testIp() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->ip('err');
        $this->assertEquals("", $this->map($v->run(['key' => '10.10.2.84'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'http://10.10.2.84'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'FE80:0000:0000:0000:0202:B3FF:FE1E:8329'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'FE80::0202:B3FF:FE1E:8329'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.12.'])));
        $this->assertEquals("", $this->map($v->run(['key' => ''])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.11.2017'])));
    }
    public function testUrl() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->url(null, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => 'http://10.10.2.84'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'https://10.10.2.84'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'https://test.com/asdf.doc?df=1#fragment'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.12'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '/a/b/v'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'mailto:/a/b/v'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '11.11.2017'])));
    }
    public function testMod10() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->mod10('err');
        $this->assertEquals("", $this->map($v->run(['key' => '79927398713'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '79927398711'])));
        $this->assertEquals("", $this->map($v->run(['key' => '4012888888881881'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '4012888888881882'])));
    }
    public function testImei() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->imei('err');
        $this->assertEquals("", $this->map($v->run(['key' => '79927398713'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '79927398711'])));
    }
    public function testCreditcard() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->creditCard(null, 'err');
        $this->assertEquals("", $this->map($v->run(['key' => '5376 7473 9720 8720'])));
        $this->assertEquals("", $this->map($v->run(['key' => '4024.0071.5336.1885'])));
        $this->assertEquals("", $this->map($v->run(['key' => '4024 007 193 879'])));
        $this->assertEquals("", $this->map($v->run(['key' => '340-3161-9380-9364'])));
        $this->assertEquals("", $this->map($v->run(['key' => '30351042633884'])));
        $this->assertEquals("", $this->map($v->run(['key' => '6011000990139424'])));
        $this->assertEquals("", $this->map($v->run(['key' => '3566002020360505'])));
        $this->assertEquals("", $this->map($v->run(['key' => ''])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'not a credit card number'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1234 1234 1234 1234'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1234.1234.1234.1234'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '6011111111111111'])));
    }
    public function testIban() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->iban('err');
        $this->assertEquals("", $this->map($v->run(['key' => 'AL47 2121 1009 0000 0002 3569 8741'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'AD12 0001 2030 2003 5910 0100'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'AT61 1904 3002 3457 3201'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'AZ21 NABZ 0000 0000 1370 1000 1944'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'BH67 BMAG 0000 1299 1234 56'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'BE62 5100 0754 7061'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'BA39 1290 0794 0102 8494'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'BG80 BNBG 9661 1020 3456 78'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'HR12 1001 0051 8630 0016 0'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'CY17 0020 0128 0000 0012 0052 7600'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'CZ65 0800 0000 1920 0014 5399'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'DK50 0040 0440 1162 43'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'EE38 2200 2210 2014 5685'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'FO97 5432 0388 8999 44'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'FI21 1234 5600 0007 85'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'FR14 2004 1010 0505 0001 3M02 606'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'GE29 NB00 0000 0101 9049 17'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'DE89 3704 0044 0532 0130 00'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'GI75 NWBK 0000 0000 7099 453'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'GR16 0110 1250 0000 0001 2300 695'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'GL56 0444 9876 5432 10'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'HU42 1177 3016 1111 1018 0000 0000'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'IS14 0159 2600 7654 5510 7303 39'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'IE29 AIBK 9311 5212 3456 78'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'IL62 0108 0000 0009 9999 999'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'IT40 S054 2811 1010 0000 0123 456'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'JO94 CBJO 0010 0000 0000 0131 0003 02'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'KW81 CBKU 0000 0000 0000 1234 5601 01'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'LV80 BANK 0000 4351 9500 1'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'LB62 0999 0000 0001 0019 0122 9114'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'LI21 0881 0000 2324 013A A'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'LT12 1000 0111 0100 1000'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'LU28 0019 4006 4475 0000'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'MK072 5012 0000 0589 84'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'MT84 MALT 0110 0001 2345 MTLC AST0 01S'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'MU17 BOMM 0101 1010 3030 0200 000M UR'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'MD24 AG00 0225 1000 1310 4168'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'MC93 2005 2222 1001 1223 3M44 555'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'ME25 5050 0001 2345 6789 51'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'NL39 RABO 0300 0652 64'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'NO93 8601 1117 947'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'PK36 SCBL 0000 0011 2345 6702'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'PL60 1020 1026 0000 0422 7020 1111'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'PT50 0002 0123 1234 5678 9015 4'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'QA58 DOHB 0000 1234 5678 90AB CDEF G'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'RO49 AAAA 1B31 0075 9384 0000'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'SM86 U032 2509 8000 0000 0270 100'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'SA03 8000 0000 6080 1016 7519'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'RS35 2600 0560 1001 6113 79'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'SK31 1200 0000 1987 4263 7541'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'SI56 1910 0000 0123 438'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'ES80 2310 0001 1800 0001 2345'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'SE35 5000 0000 0549 1000 0003'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'CH93 0076 2011 6238 5295 7'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'TN59 1000 6035 1835 9847 8831'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'TR33 0006 1005 1978 6457 8413 26'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'AE07 0331 2345 6789 0123 456'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'GB82 WEST 1234 5698 7654 32'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'AE07 0331 2345 6789 0123 451'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'asdf'])));
    }

    public function testUuid() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->uuid('err');
        $this->assertEquals("", $this->map($v->run(['key' => '130c4bdf-be37-4257-a05f-7ec61fc73c98'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'e6c165d7-93ec-49da-8df0-48a0d5a9deec'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'e6c165d7-93ec-49da-8df0-48a0d5a9dee'])));
    }
    public function testMac() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->mac('err');
        $this->assertEquals("", $this->map($v->run(['key' => '00-14-22-01-23-45'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '00-14-22-01-23-451'])));
    }
    public function testBgEGN() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->bgEGN('err');
        $this->assertEquals("", $this->map($v->run(['key' => '1111111110'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1111111111'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1181111110'])));
    }
    public function testBgLNC() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->bgLNC('err');
        $this->assertEquals("", $this->map($v->run(['key' => '1111111111'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1111111110'])));
    }
    public function testBgIDN() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->bgIDN('err');
        $this->assertEquals("", $this->map($v->run(['key' => '1111111111'])));
        $this->assertEquals("", $this->map($v->run(['key' => '1111111110'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1111111112'])));
    }
    public function testBgMaleEGN() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->bgMaleEgn('err');
        $this->assertEquals("", $this->map($v->run(['key' => '1111111125'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1111111110'])));
    }
    public function testBgFemaleEGN() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->bgFemaleEgn('err');
        $this->assertEquals("", $this->map($v->run(['key' => '1111111110'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '1111111125'])));
    }
    public function testBgBulstat() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->bgBulstat('err');
        $this->assertEquals("", $this->map($v->run(['key' => '119044990'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '119044991'])));
    }
    public function testBgName() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->bgName('err');
        $this->assertEquals("", $this->map($v->run(['key' => 'Иван Петров'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'Иван Георгиев Петров'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'Мария-Венцислава Георгиева Петрова'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'Мария-Венцислава Георгиева Петрова-Карабашибозукова'])));
        $this->assertEquals("", $this->map($v->run(['key' => 'Мария Иванова Петрова Гошева'])));
        $this->assertEquals("err", $this->map($v->run(['key' => 'Мария'])));
    }
    public function testSerialize()
    {
        $v = new \vakata\validation\Validator();
        $v
            ->required('name', 'requiredN')
            ->required('family', 'requiredF')->alpha(null, "alphaF")
            ->optional("children.*.name")->alpha("asfd", "alphaC");
        $this->assertEquals(
            [
                'name' => [
                    [ 'rule' => 'required', 'message' => 'requiredN', 'data' => [], 'when' => null ]
                ],
                'family' => [
                    [ 'rule' => 'required', 'message' => 'requiredF', 'data' => [], 'when' => null ],
                    [ 'rule' => 'alpha', 'message' => 'alphaF', 'data' => [ null ], 'when' => null ],
                ],
                'children.*.name' => [
                    [ 'rule' => 'alpha', 'message' => 'alphaC', 'data' => [ "asfd" ], 'when' => null ],
                ]
            ],
            json_decode(json_encode($v), true)
        );
    }
    public function testRemove() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->numeric('err');
        $this->assertEquals("", $this->map($v->run(['key' => '12'])));
        $v->remove('key');
        $this->assertEquals("", $this->map($v->run(['key' => '1a'])));
    }
    public function testRemoveRule() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->numeric('err')->alpha(true, 'alp');
        $this->assertEquals("err,alp", $this->map($v->run(['key' => '1a'])));
        $v->remove('key', 'alpha');
        $this->assertEquals("err", $this->map($v->run(['key' => '1a'])));
        $v->remove('key', 'numeric');
        $this->assertEquals("", $this->map($v->run(['key' => '1a'])));
    }
    public function testMinRelation() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->minRelation('rel', 'err');
        $this->assertEquals("err", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '0', 'rel' => '1'])));
        $this->assertEquals("", $this->map($v->run(['key' => '2', 'rel' => '2'])));
        $this->assertEquals("", $this->map($v->run(['key' => '3', 'rel' => '2'])));
    }
    public function testMaxRelation() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->maxRelation('rel', 'err');
        $this->assertEquals("err", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '2', 'rel' => '1'])));
        $this->assertEquals("", $this->map($v->run(['key' => '2', 'rel' => '2'])));
        $this->assertEquals("", $this->map($v->run(['key' => '1', 'rel' => '2'])));
    }
    public function testEqualsRelation() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->equalsRelation('rel', 'err');
        $this->assertEquals("err", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '2', 'rel' => '1'])));
        $this->assertEquals("", $this->map($v->run(['key' => '2', 'rel' => '2'])));
    }
    public function testMinDateRelation() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->minDateRelation('rel', null, 'err');
        $this->assertEquals("err", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '01.01.2017', 'rel' => '02.01.2017'])));
        $this->assertEquals("", $this->map($v->run(['key' => '03.01.2017', 'rel' => '02.01.2017'])));
    }
    public function testMaxDateRelation() {
        $v = new \vakata\validation\Validator();
        $v->optional('key')->maxDateRelation('rel', null, 'err');
        $this->assertEquals("err", $this->map($v->run(['key' => '2'])));
        $this->assertEquals("err", $this->map($v->run(['key' => '03.01.2017', 'rel' => '02.01.2017'])));
        $this->assertEquals("", $this->map($v->run(['key' => '01.01.2017', 'rel' => '02.01.2017'])));
    }
    public function testGlobalCondition()
    {
        $v = new \vakata\validation\Validator();
        $v
            ->condition(function ($val, $data) { return false; })
                ->required('key1', 'req1')
            ->condition(function ($val, $data) { return true; })
                ->required('key2', 'req2')
            ->condition()
                ->required('key3', 'req3');
        $this->assertEquals("req2,req3", $this->map($v->run([])));
    }
    public function testContext()
    {
        $v = new \vakata\validation\Validator();
        $v
            ->condition(function ($val, $data, $context) { return $context === 'insert'; })
                ->required('key1', 'req1')
            ->condition()
                ->required('key2', 'req2');
        $this->assertEquals("req2", $this->map($v->run([])));
        $this->assertEquals("req1,req2", $this->map($v->run([], 'insert')));
    }
    public function testNestedValidator()
    {
        $v = new \vakata\validation\Validator();
        $i = new \vakata\validation\Validator();
        $i
            ->required('key1')->equals('1', 'need 1')
            ->required('key2')->equals('2', 'need 2');
        $v
            ->condition($i)
                ->required('key3', 'req1')
            ->condition()
                ->required('key3', 'req2');
        $this->assertEquals("req2", $this->map($v->run([])));
        $this->assertEquals("req2", $this->map($v->run(['key1'=>'1'])));
        $this->assertEquals("req2", $this->map($v->run(['key2'=>'2'])));
        $this->assertEquals("req1,req2", $this->map($v->run(['key1'=>'1','key2'=>'2'])));
    }
    public function testNestedRelativeValidator()
    {
        $v = new \vakata\validation\Validator();
        $i = new \vakata\validation\Validator();
        $i
            ->required('.name')->equals('1');
        $v
            ->condition($i)
                ->required('key.num', 'req');
        $this->assertEquals("", $this->map($v->run([])));
        $this->assertEquals("", $this->map($v->run(['key'=>'1'])));
        $this->assertEquals("", $this->map($v->run(['key'=>['name' => '1', 'num' => '2']])));
        $this->assertEquals("req", $this->map($v->run(['key'=>['name' => '1', 'num' => '']])));
        $this->assertEquals("req", $this->map($v->run(['key'=>['name' => '1']])));
    }
    public function testDisabledNestedRelativeValidator()
    {
        $v = new \vakata\validation\Validator();
        $i = new \vakata\validation\Validator();
        $i
            ->required('.name')->equals('1')
            ->required('.name')->equals('2');
        $v
            ->condition($i)
                ->required('key.num', 'req');
        $this->assertEquals("", $this->map($v->run([])));
        $this->assertEquals("", $this->map($v->run(['key'=>'1'])));
        $this->assertEquals("", $this->map($v->run(['key'=>['name' => '1', 'num' => '2']])));
        $this->assertEquals("", $this->map($v->run(['key'=>['name' => '1', 'num' => '']])));
        $this->assertEquals("", $this->map($v->run(['key'=>['name' => '1']])));
        $i->rules('.name')[3]->disable();
        $this->assertEquals("", $this->map($v->run([])));
        $this->assertEquals("", $this->map($v->run(['key'=>'1'])));
        $this->assertEquals("", $this->map($v->run(['key'=>['name' => '1', 'num' => '2']])));
        $this->assertEquals("req", $this->map($v->run(['key'=>['name' => '1', 'num' => '']])));
        $this->assertEquals("req", $this->map($v->run(['key'=>['name' => '1']])));
    }
    public function testAdd() {
        $v = new \vakata\validation\Validator();
        $v
            ->add('name', 'required', 'requiredN')
            ->add('name', 'alpha', null, "alphaN")
            ->add('name', 'notEmpty', "empty")
            ->add('family', 'required', 'requiredF')
            ->add('family', 'alpha', null, "alphaF")
            ->add('age', 'required', 'requiredA')
            ->add('age', 'numeric', "numericA")
            ->addOptional("newsletter", 'numeric', "numericN")
            ->addOptional("children.*.name", 'alpha', null, "alphaC")
            ->addOptional("children.*.age", 'numeric', "numericC");

        $this->assertEquals("requiredN,empty,requiredF,requiredA,numericA", $this->map($v->run(null)));
        $this->assertEquals(
            "requiredN,empty,requiredF,requiredA,numericA",
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
    public function testDefaults()
    {
        $v = new \vakata\validation\Validator();
        $i = new \vakata\validation\Validator();
        $i
            ->required('.name')->equals('1');
        $v
            ->default()->numeric('numeric')
            ->condition($i)
                ->required('key.num', 'req');
        $this->assertEquals("", $this->map($v->run([])));
        $this->assertEquals("", $this->map($v->run(['key'=>'1'])));
        $this->assertEquals("", $this->map($v->run(['key'=>['name' => '1', 'num' => '2']])));
        $this->assertEquals("numeric", $this->map($v->run(['key'=>['name' => '1', 'num' => 'a']])));
        $this->assertEquals("numeric,req", $this->map($v->run(['key'=>['name' => '1']])));
    }
    public function testConditionalDefaults()
    {
        $v = new \vakata\validation\Validator();
        $i = new \vakata\validation\Validator();
        $g = new \vakata\validation\Validator();
        $i->required('.name')->equals('1');
        $g->required('cond')->equals('1');
        $v
            ->default()
                ->condition($g)
                ->numeric('numeric')
            ->condition($i)
                ->required('key.num', 'req');
        $this->assertEquals("", $this->map($v->run([])));
        $this->assertEquals("", $this->map($v->run(['key'=>'1'])));
        $this->assertEquals("", $this->map($v->run(['key'=>['name' => '1', 'num' => '2']])));
        $this->assertEquals("", $this->map($v->run(['key'=>['name' => '1', 'num' => 'a']])));
        $this->assertEquals("req", $this->map($v->run(['key'=>['name' => '1']])));
        $this->assertEquals("numeric", $this->map($v->run(['key'=>['name' => '1', 'num' => 'a'], 'cond' => 1])));
        $this->assertEquals("numeric,req", $this->map($v->run(['key'=>['name' => '1'], 'cond' => 1])));
    }
    public function testRemoveDefaults()
    {
        $v = new \vakata\validation\Validator();
        $i = new \vakata\validation\Validator();
        $i
            ->required('.name')->equals('1');
        $v
            ->default()->numeric('numeric')
            ->condition($i)
                ->required('key.num', 'req')
                ->remove('key.num', 'numeric');
        $this->assertEquals("", $this->map($v->run([])));
        $this->assertEquals("", $this->map($v->run(['key'=>'1'])));
        $this->assertEquals("", $this->map($v->run(['key'=>['name' => '1', 'num' => '2']])));
        $this->assertEquals("", $this->map($v->run(['key'=>['name' => '1', 'num' => 'a']])));
        $this->assertEquals("req", $this->map($v->run(['key'=>['name' => '1']])));
    }
}
