<?php

    class SVG_Abstract {
        const INDENT_SIZE = 4;
        
        protected $_attributes = array();
        
        protected $_elements = array();
        
        protected function _renderElements($indent_level) {
            $code = null;
            
            foreach ($this->_elements as $elem) {
                $code .= $elem->render($indent_level);
            }
            
            return $code;
        }
        
        protected function _renderElement($tag, $attrs = array(), $content = null, $indent_level, $force_content = false) {
            $elem = PHP_EOL;
            
            $indent = $indent_level * self::INDENT_SIZE;
            $elem .= str_repeat(' ', $indent);
            
            $elem .= '<';
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
                
                $elem .= PHP_EOL;
                
                $elem .= str_repeat(' ', $indent);
                $elem .= '</' . $tag . '>';
            } else {
                $elem .= ' />';
            }
            
            return $elem;
        }
        
        public function attr($name, $value) {
            $this->_attributes[$name] = $value;
            return $this;
        }
        
        public function __get($name) {
            $elem = new SVG_Element($this, $name);
            $this->_elements[] = $elem;
                
            return $elem;
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
            
            $content = $this->_renderElements(1);
            
            $attrs = array(
                'version'     => '1.1',
                'baseProfile' => 'full',
                'xmlns'       => 'http://www.w3.org/2000/svg', 
                'xmlns:xlink' => 'http://www.w3.org/1999/xlink',
                'xmlns:ev'    => 'http://www.w3.org/2001/xml-events',
                'height'      => '400px',
                'width'       => '400px'
            );
            $svg .= $this->_renderElement('svg', $attrs, $content, 0, true);
            
            return $svg;
        }
    }
    
    class SVG_Element extends SVG_Abstract {
        protected $_parent;
        
        protected $_tag;
        
        public function __construct(SVG_Abstract $parent, $tag) {
            $this->_parent = $parent;
            $this->_tag = $tag;
        }
        
        public function parent() {
            return $this->_parent;
        }
        
        public function render($indent_level) {
            $content = $this->_renderElements($indent_level + 1);
            return $this->_renderElement($this->_tag, $this->_attributes, $content, $indent_level);
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
            ->attr('stroke-opacity', 0.5)
            ->parent()
            
        ->g
            ->attr('fill-opacity', 0.7)
            ->stroke('black')
            ->attr('stroke-width', '0.5px')
                
                ->circle
                    ->cx('200px')
                    ->cy('200px')
                    ->r('100px')
                    ->fill('red')
                    ->transform('translate(0,-50)')
                    ->parent()
                
                ->circle
                    ->cx('200px')
                    ->cy('200px')
                    ->r('100px')
                    ->fill('blue')
                    ->transform('translate(70, 50)')
                    ->parent()
                
                ->circle
                    ->cx('200px')
                    ->cy('200px')
                    ->r('100px')
                    ->fill('green')
                    ->transform('translate(-70, 50)')
                    ->parent()
    ;
    
    $svg->render('./gen-test.svg');

?>