<?php

    class SVG_Abstract {
        //const INDENT_SIZE = 4;
        
        protected $_attributes = array();
        
        protected $_elements = array();
        
        protected function _renderElements() {
            $code = null;
            
            foreach ($this->_elements as $elem) {
                $code .= $elem->render();
            }
            
            return $code;
        }
        
        protected function _renderElement($tag, $attrs = array(), $content = null, $force_content = false) {
            $elem = '<';
            $elem .= $tag;
            
            if (sizeof($attrs))
            {
                foreach ($attrs as $name => $value) {
                    $elem .= ' ';
                    $elem .= $name . '="' . $value . '"';
                }
            }
            
            if (null !== $content || $force_content) {
                $elem .= '>';
                $elem .= $content;
                $elem .= '</' . $tag . '>';
            } else {
                $elem .= '/>';
            }
            
            return $elem;
        }
        
        public function __get($name) {
            if (!isset($this->_elements[$name]))
            {
                $elem = new SVG_Element($name);
                $this->_elements[] = $elem;
                
                return $elem;
            }
            
            return $this->_elements[$name];
        }
        
        public function attr($name, $value) {
            $this->_attributes[$name] = $value;
            return $this;
        }
        
        public function __call($name, $arguments) {
            return $this->attr($name, array_shift($arguments));
        }
    }

    class SVG_Document extends SVG_Abstract {
        public function render($filepath = null) {
            $doc = $this->_render();
            
            if (!null !== $filepath) {
                return file_put_contents($filepath, $doc);
            }
            
            return $svg;
        }
        
        protected function _render() {
            $svg = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';
            $svg .= PHP_EOL;
            
            $content = $this->_renderElements();
            
            $attrs = array(
                'version'     => '1.1',
                'baseProfile' => 'full',
                'xmlns'       => 'http://www.w3.org/2000/svg', 
                'xmlns:xlink' => 'http://www.w3.org/1999/xlink',
                'xmlns:ev'    => 'http://www.w3.org/2001/xml-events',
                'height'      => '400px',
                'width'       => '400px'
            );
            $svg .= $this->_renderElement('svg', $attrs, $content, true);
            
            return $svg;
        }
    }
    
    class SVG_Element extends SVG_Abstract {
        protected $_tag;
        
        public function __construct($tag) {
            $this->_tag = $tag;
        }
        
        public function render() {
            $content = $this->_renderElements();
            return $this->_renderElement($this->_tag, $this->_attributes, $content);
        }
    }
    
    $svg = new SVG_Document();
    
    $svg
        ->rect
            ->x(0)
            ->y(0)
            ->width(400)
            ->height(400)
            ->fill('none')
            ->stroke('black')
            ->attr('stroke-width', '5px')
            ->attr('stroke-opacity', 0.5);
    
    $svg->render('./gen-test.svg');

?>