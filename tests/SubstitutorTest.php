<?php
/**
 * Created by PhpStorm.
 * User: lukaswhite
 * Date: 23/10/2018
 * Time: 10:52
 */

class SubstitutorTest extends \PHPUnit\Framework\TestCase
{
    public function testSimple( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear [[FORENAME]] [[SURNAME]],',
            [
                'FORENAME'  =>  'Joe',
                'SURNAME'   =>  'Bloggs',
            ]
        );

        $this->assertEquals( 'Dear Joe Bloggs,', $substitutor->run( ) );
    }

    public function testSimpleLowercaseTokens( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear [[FORENAME]] [[SURNAME]],',
            [
                'forename'  =>  'Joe',
                'surname'   =>  'Bloggs',
            ]
        );

        $this->assertEquals( 'Dear Joe Bloggs,', $substitutor->run( ) );
    }

    public function testSettingOpeningTag( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear ^FORENAME^ ^SURNAME^,',
            [
                'FORENAME'  =>  'Joe',
                'SURNAME'   =>  'Bloggs',
            ]
        );

        $substitutor->setOpeningTag( '^' );

        $this->assertEquals( 'Dear Joe Bloggs,', $substitutor->run( ) );
    }

    public function testSettingOpeningTagAndClosingTag( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear {{FORENAME}} {{SURNAME}},',
            [
                'FORENAME'  =>  'Joe',
                'SURNAME'   =>  'Bloggs',
            ]
        );

        $substitutor->setOpeningTag( '{{' )->setClosingTag( '}}' );

        $this->assertEquals( 'Dear Joe Bloggs,', $substitutor->run( ) );
    }

    public function testMissingTokensReplacedByEmptyStrings( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear [[FORENAME]] [[SURNAME]],',
            [
                'FORENAME'  =>  'Joe',
            ]
        );


        $this->assertEquals( 'Dear Joe ,', $substitutor->run( ) );
    }

    public function testGetAvailableTokens( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear {{FORENAME}} {{SURNAME}},',
            [
                'FORENAME'  =>  'Joe',
                'SURNAME'   =>  'Bloggs',
            ]
        );

        $this->assertEquals( [ 'FORENAME', 'SURNAME' ], $substitutor->getAvailableTokens( ) );
    }

    public function testClearTokens( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear {{FORENAME}} {{SURNAME}},',
            [
                'FORENAME'  =>  'Joe',
                'SURNAME'   =>  'Bloggs',
            ]
        );

        $substitutor->clearTokens( );

        $this->assertEquals( [  ], $substitutor->getAvailableTokens( ) );
    }

    public function testStringWithoutTokensRemainsUnchanged( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear guest,',
            [
                'FORENAME'  =>  'Joe',
                'SURNAME'   =>  'Bloggs',
            ]
        );

        $this->assertEquals( 'Dear guest,', $substitutor->run( ) );
    }

    public function testMagicStringMethodReturnsSubstitutedContent( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear [[FORENAME]] [[SURNAME]],',
            [
                'FORENAME'  =>  'Joe',
                'SURNAME'   =>  'Bloggs',
            ]
        );

        $this->assertEquals( 'Dear Joe Bloggs,', ( string ) $substitutor );
    }

    public function testTokenAsObject( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear [[NAME]],',
            [
                'NAME'  =>  new Person( 'Joe', 'Bloggs' ),
            ]
        );

        $this->assertEquals( 'Dear Joe Bloggs,', ( string ) $substitutor );
    }

    public function testValidation( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear [[FORENAME]] [[SURNAME]],',
            [
                'FORENAME'  =>  'Joe',
                'SURNAME'   =>  'Bloggs',
            ]
        );

        $this->assertTrue( $substitutor->validate( ) );
    }

    public function testValidationFail( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear [[FORENAME]] [[SURNAME]],',
            [
                'FORENAME'  =>  'Joe',
            ]
        );

        $this->assertFalse( $substitutor->validate( ) );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Tokens must only contain letters, numbers, dashes or underscores
     */
    public function testExceptionThrownIfTokenHasInvalidCharacters( )
    {
        $substitutor = new \Lukaswhite\TokenStrings\Substitutor(
            'Dear {{FORENAME}} {{SURNAME}},',
            [
                'FORENAME'  =>  'Joe',
                'su*name'   =>  'Bloggs',
            ]
        );
    }
}

class Person {

    private $forename;

    private $surname;

    public function __construct( $forename, $surname ) {
        $this->forename = $forename;
        $this->surname = $surname;
    }

    public function __toString( ) {
        return sprintf( '%s %s', $this->forename, $this->surname );
    }
}