<?php

namespace com\github\tncrazvan\AsciiTable;

class AsciiCel{
    private $numberOfLines = 0;
    private $width;
    private $height;
    private $top;
    private $bottom;
    private $spaceer;
    private $data = [];
    private $originalString;
    private $options = [
        "width" => 0,
        "max-width" => 15,
        "padding-left" => 1,
        "padding-right" => 1,
        "padding-top" => 0,
        "padding-bottom" => 0,
        "padding-between-lines-top" => 0,
        "padding-between-lines-bottom" => 0
    ];
    public function __construct(string $data,array &$options=[]){
        $data = \preg_replace("/\\t/",\str_repeat(" ",4),$data);
        $data = \preg_replace("/\\r/",'',$data);
        $this->originalString = $data;
        foreach($options as $key => &$value){
            $this->options[$key] = $value;
        }
        $this->parseOptions();
        $this->data = [];
        $length = strlen($data);
        if($length > $this->options["max-width"]){
            $string = "";
            $position = 0;
            for($i=0;$i < $length; $i++){
                $position = $i % $this->options["max-width"];
                if($position+1 > $this->width)
                    $this->width = $position+1;
                if($i > 0 && $position === 0){
                    $this->data[] = $string;
                    $string = "";
                }
                $string .= $data[$i];
            }
            $this->data[] = $string;
        }else{
            $this->data[] = $data;
        }
    }

    public function getHeight():int{
        return $this->height;
    }

    public function getWidth():int{
        return $this->width;
    }
    public function setWidth(int $width):void{
        $this->width = $width;
    }
    public function increaseWidth(int $width):void{
        $this->width += $width;
    }
    public function decreaseWidth(int $width):void{
        $this->width -= $width;
    }

    public function getLines():array{
        return $this->resolve();
    }

    private function parseOptions():void{
        foreach($this->options as $key => &$value){
            switch($key){
                case "padding":
                    $this->options["padding-left"] = $value;
                    $this->options["padding-right"] = $value;
                    $this->options["padding-top"] = $value;
                    $this->options["padding-bottom"] = $value;
                break;
                case "padding-between-lines":
                    $this->options["padding-between-lines-left"] = $value;
                    $this->options["padding-between-lines-right"] = $value;
                    $this->options["padding-between-lines-top"] = $value;
                    $this->options["padding-between-lines-bottom"] = $value;
                break;
            }
        }
    }

    public function getOriginalString():string{
        return $this->originalString;
    }

    public function resolve():array{
        $tmp = [];
        $length = count($this->data);
        for($i=0;$i<$length;$i++){
            $dataLen = strlen($this->data[$i]);
            if($this->width < $dataLen)
                $this->width = $dataLen;
        }
        
        $this->top = str_repeat("-",$this->width);
        $this->bottom = str_repeat("-",$this->width);
        $this->spacer = str_repeat(" ",$this->width);

        $this->insertLineInTmp($this->top,$tmp,"+",true,true);
        for($j=0;$j<$this->options["padding-top"];$j++){
            $this->insertLineInTmp($this->spacer,$tmp,"|",true,true);
        }
        for($i=0;$i<$length;$i++){
            for($j=0;$j<$this->options["padding-between-lines-top"];$j++){
                $this->insertLineInTmp($this->spacer,$tmp,"|",true,true);
            }
            $this->insertLineInTmp($this->data[$i],$tmp);
            for($j=0;$j<$this->options["padding-between-lines-bottom"];$j++){
                $this->insertLineInTmp($this->spacer,$tmp,"|",true,true);
            }
        }
        for($j=0;$j<$this->options["padding-bottom"];$j++){
            $this->insertLineInTmp($this->spacer,$tmp,"|",true,true);
        }
        $this->insertLineInTmp($this->bottom,$tmp,"+",true,true);
        return $tmp;
    }

    private function insertLineInTmp(string $data, array &$tmp, string $sideString="|", bool $extendFirstCharacter = false, bool $extendRightCharacter=false):void{
        if(\preg_match("/\\n/",$data)){
            $split = preg_split("/\\n/",$data);
            foreach($split as &$extraRowData){
                $this->insertLineInTmp($extraRowData,$tmp,$sideString,$extendFirstCharacter,$extendRightCharacter);
            }
            return;
        }
        $paddingLeft = str_repeat($extendFirstCharacter?$data[0]:" ",$this->options["padding-left"]);
        $paddingRight = str_repeat($extendRightCharacter?$data[-1]:" ",$this->options["padding-right"]);
        $len = strlen($data);
        if($len > $this->width){
            $this->width = $len;
        }else if($len < $this->width){
            $data .= str_repeat(" ",$this->width - $len);
        }
        $tmp[] = $sideString.$paddingLeft.$data.$paddingRight.$sideString;
        $this->height++;
    }
}