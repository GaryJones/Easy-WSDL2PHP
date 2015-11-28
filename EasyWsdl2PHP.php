<?php

class EasyWsdl2PHP
{
    static public function generate($url, $sname)
    {
        $soapClient = new SoapClient($url);
        $classesArr = [];
        $functions = $soapClient->__getFunctions();
        $nl = "\n";
        $tab = "    ";
        $code        = '';
        $simpletypes = ['string', 'int', 'double', 'dateTime', 'float'];
        foreach ($functions as $func) {
            $temp = explode(' ', $func, 2);
            //less process whateever is inside ()
            $start      = strpos($temp[1], '(');
            $end        = strpos($temp[1], '(');
            $parameters = substr($temp[1], $start, $end);
            $t1   = str_replace(')', '', $temp[1]);
            $t1   = str_replace('(', ':', $t1);
            $t2   = explode(':', $t1);
            $func = $t2[0];
            $par  = $t2[1];
            $params = explode(' ', $par);
            $p1     = '$' . $params[0];
            $code .= $nl . $tab . 'public function ' . $func . '(' . $p1 . ')'
                     . "{$nl}{$tab}{";
            if ($temp[0] == 'void') {
                $code .= $nl . "\$this->soapClient->$func({$p1});{$nl}}";
            } else {
                $code .= $nl . $tab . $tab . "return \$this->soapClient->$func({$p1});{$nl}{$tab}}\n";
            }
        }
        $code .= "}\n{$nl}";
        //    print_r($functions);
        //    echo "<hr>";

        $types = $soapClient->__getTypes();
        // print_r($types);
        $codeType = '';
        foreach ($types as $type) {
            if (substr($type, 0, 6) == 'struct') {
                $data         = trim(str_replace(['{', '}'], '', substr($type, strpos($type, '{') + 1)));
                $data_members = explode(';', $data);
                //print_r($data_members);
                // echo "[" . $data . "]";
                $classname = trim(substr($type, 6, strpos($type, '{') - 6));
                //write object
                $codeType .= $nl . 'class ' . $classname . $nl . '{';
                $classesArr [] = $classname;
                foreach ($data_members as $member) {
                    $member = trim($member);
                    if (strlen($member) < 1) {
                        continue;
                    }
                    list($data_type, $member_name) = explode(' ', $member);
                    $codeType .= "{$nl}{$tab}protected \${$member_name};//{$data_type}";
                }
                $codeType .= $nl . '}' . $nl;
            }
        }

        $mapstr       = "\n" . $tab . 'private static $classmap = [';
        $classMAPCode = [];
        foreach ($classesArr as $cname) {
            // $mapstr .= "\n,'$cname'=>'$cname'";
            $classMAPCode[] = "\n{$tab}{$tab}'$cname'=>'$cname',";
        }
        //print_r($classMAPCode);
        $mapstr .= implode('', $classMAPCode);
        $mapstr .= "\n" . $tab . '];';
        
        $fullcode = <<< EOT
<?php
$codeType

class $sname
{
    protected \$soapClient;
    $mapstr

    public function __construct(\$url='{$url}')
    {
        \$this->soapClient = new SoapClient(\$url,array("classmap"=>self::\$classmap,"trace" => true,"exceptions" => true));
    }
    $code
EOT;
        return $fullcode;
    }
}
