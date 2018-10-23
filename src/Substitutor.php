<?php

namespace Lukaswhite\TokenStrings;

/**
 * Class Substitutor
 *
 * This class replaces tokens within a string at runtime.
 *
 * For example, suppose you have the following string:
 *
 * Dear [[FORENAME]] [[SURNAME]],
 *
 * The idea is that you can provide the forename and surname at runtime, e.g.
 *
 * [
 *  'FORENAME'  =>  'Joe',
 * 'SURNAME'   =>  'Bloggs',
 * ]
 *
 * Resulting in:
 *
 * Dear Joe Bloggs,
 *
 * @package Lukaswhite\TokenStrings
 */
class Substitutor
{
    /**
     * The tokens
     *
     * @var array
     */
    protected $tokens;

    /**
     * The content of the string, before the tokens have been substituted
     *
     * @var string
     */
    protected $content;

    /**
     * Transformers for altering a parameters output
     *
     * @var array
     */
    protected $transformers;

    /**
     * List of pseudo fields
     *
     * @var array
     */
    private $pseudoFields = [];

    /**
     * The opening tag
     *
     * @var string
     */
    protected $openingTag = '[[';

    /**
     * The closing tag
     *
     * @var string
     */
    protected $closingTag = ']]';

    /**
     * Substitutor constructor.
     *
     * @param  string  $content
     * @param  array  $tokens
     */
    public function __construct($content = null, $tokens = [])
    {
        if ( is_string( $content ) )
        {
            $this->setContent( $content );
        }

        if ( ! empty( $tokens ) )
        {
            foreach( $tokens as $token => $value )
            {
                $this->addToken( $token, $value );
            }
        }
    }

    /**
     * Run the substitution process
     *
     * @return string
     */
    public function run()
    {
        $tokens = $this->extractTokens( );

        // Loop through all the matches
        foreach( $tokens as $token )
        {
            if ( ! isset( $this->tokens[ $token ] ) )
            {
                $this->tokens[ $token ] = '';
            }

            if (
                is_object( $this->tokens[ $token ] ) &&
                method_exists( $this->tokens[ $token ], '__toString' ) ) {
                $replacement = ( string ) $this->tokens[ $token ];
            } else {
                $replacement = $this->tokens[ $token ];
            }

            // Get the content and replace the token with the parameter token
            $this->content = str_ireplace(
                sprintf( '%s%s%s', $this->openingTag, strtoupper( $token ), $this->closingTag ),
                $replacement,
                $this->content
            );
        }

        // Return the content
        return $this->content;
    }

    /**
     * Add a new token
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @throws \InvalidArgumentException
     *
     * @return Substitutor
     */
    public function addToken( $key, $value )
    {
        $key = strtoupper( $key );

        if ( ! preg_match( '/^[A-Z-_0-9]+$/', $key ) ) {
            throw new \InvalidArgumentException(
                'Tokens must only contain letters, numbers, dashes or underscores'
            );
        }

        $this->tokens[ strtoupper( $key ) ] = $value;

        return $this;
    }

    /**
     * Clear the tokens
     *
     * @return self
     */
    public function clearTokens( )
    {
        $this->tokens = [ ];
        return $this;
    }

    /**
     * Set the content.
     *
     * @param  string  $content
     *
     * @return Substitutor
     */
    public function setContent( $content )
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the opening tags
     *
     * @param string $tag
     * @return $this
     */
    public function setOpeningTag( $tag = '[[' )
    {
        $this->openingTag = $tag;
        $this->closingTag = strrev( $this->openingTag );
        return $this;
    }

    /**
     * Set the closing tags
     *
     * @param string $tag
     * @return $this
     */
    public function setClosingTag( $tag = ']]' )
    {
        $this->closingTag = $tag;

        return $this;
    }

    /**
     * Get all of the available tokens
     *
     * @return array
     */
    public function getAvailableTokens()
    {
        return array_keys( $this->tokens );
    }

    /**
     * Get a list of tokens in the content. Excluding duplicates.
     *
     * @return array
     */
    public function extractTokens()
    {
        // Prepare the opening and closing tags for the regex
        $openingTag = sprintf( '\\%s', implode( '\\', str_split( $this->openingTag ) ) );
        $closingTag = sprintf( '\\%s', implode( '\\', str_split( $this->closingTag ) ) );

        // Build the regex
        $regex = sprintf( '/%s([A-Z-_0-9]+)%s/i', $openingTag, $closingTag );

        // Find all the tokens in the string
        preg_match_all( $regex, $this->content, $matches, PREG_PATTERN_ORDER );

        // If there are no matches, simply return an empty array
        if ( empty( $matches[1] ) )
        {
            return [];
        }

        // Return a unique list of tokens
        return array_unique( $matches[ 1 ] );
    }

    /**
     * Validate the template; it's valid if all the tokens in it have corresponding values.
     *
     * @return bool
     */
    public function validate( )
    {
        return count( array_diff( $this->extractTokens( ), $this->getAvailableTokens( ) ) ) == 0;
    }

    /**
     * Magic toString method; runs the substitution process and returns a string.
     * @return string
     */
    public function __toString()
    {
        return $this->run( );
    }

}