<?php

namespace JBBCode\visitors;

/**
 * This visitor is an example of how to implement smiley parsing on the JBBCode
 * parse graph. It converts :) into image tags pointing to /smiley.png.
 *
 * @author jbowens
 * @since April 2013
 */
class SmileyVisitor implements \JBBCode\NodeVisitor
{
    protected $bbcode_smileys = array(':)'  => 'icon_smile.gif',
                                      ':D'  => 'icon_smile_big.gif',
                                      '8D'  => 'icon_smile_cool.gif',
                                      ':I'  => 'icon_smile_blush.gif',
                                      ':P'  => 'icon_smile_tongue.gif',
                                      '}:)' => 'icon_smile_evil.gif',
                                      ';)'  => 'icon_smile_wink.gif',
                                      ':o)' => 'icon_smile_clown.gif',
                                      'B)'  => 'icon_smile_blackeye.gif',
                                      '8'   => 'icon_smile_8ball.gif',
                                      ':('  => 'icon_smile_sad.gif',
                                      '8)'  => 'icon_smile_shy.gif',
                                      ':O'  => 'icon_smile_shock.gif',
                                      ':(!' => 'icon_smile_angry.gif',
                                      'xx(' => 'icon_smile_dead.gif',
                                      '|)'  => 'icon_smile_sleepy.gif',
                                      ':X'  => 'icon_smile_kisses.gif',
                                      '^'   => 'icon_smile_approve.gif',
                                      'V'   => 'icon_smile_dissapprove.gif',
                                      '?'   => 'icon_smile_question.gif',
                                );

    function visitDocumentElement(\JBBCode\DocumentElement $documentElement)
    {
        foreach($documentElement->getChildren() as $child) {
            $child->accept($this);
        }
    }

    function visitTextNode(\JBBCode\TextNode $textNode)
    {
        foreach($this->bbcode_smileys as $bbcode => $image) {
            $textNode->setValue(str_replace('[' . $bbcode . ']', '<img src="../images/icons/'.$image.'" alt="' . $bbcode . '" />', $textNode->getValue()));
        }
    }

    function visitElementNode(\JBBCode\ElementNode $elementNode)
    {
        /* We only want to visit text nodes within elements if the element's
         * code definition allows for its content to be parsed.
         */
        if ($elementNode->getCodeDefinition()->parseContent()) {
            foreach ($elementNode->getChildren() as $child) {
                $child->accept($this);
            }
        }
    }

}
