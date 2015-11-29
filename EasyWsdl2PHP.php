<?php

namespace Gamajo\EasyWsdl2PHP;

use SoapClient;

class EasyWsdl2PHP
{
    /**
     * WSDL URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Service class name.
     *
     * @var string
     */
    protected $sname;

    /**
     * Character for output indentation.
     *
     * @var string
     */
    protected $tab;
    /**
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * Holds generated classes.
     *
     * @var array
     */
    protected $classes = [];

    /**
     * EasyWsdl2PHP constructor.
     *
     * @param string $url   WSDL URL
     * @param string $sname Service class name.
     * @param string $tab   Indentation character(s).
     */
    public function __construct($url, $sname, $tab = '    ')
    {
        $this->url   = $url;
        $this->sname = $sname;
        $this->tab   = $tab;
    }

    /**
     * Helper function to instantiate this class and get the generated code.
     *
     * @param string $url   WSDL URL
     * @param string $sname Service class name.
     * @param string $tab   Indentation character(s).
     *
     * @return string
     */
    public static function generate($url, $sname, $tab = '    ')
    {
        $class = new self($url, $sname, $tab);

        return $class->getCode();
    }

    /**
     * Get the generated code.
     *
     * @return string
     */
    public function getCode()
    {
        $types = $this->getTypes();
        $classes = $this->writeClasses($types);
        $map = $this->writeMap(); // Must come after writeClasses();

        // Service class
        $functions = $this->getFunctions();
        $methods = $this->writeMethods($functions);

        $fullcode = <<< EOT
<?php
$classes
class $this->sname
{
    protected \$soapClient;
    $map

    public function __construct(\$url='{$this->url}')
    {
        \$this->soapClient = new SoapClient(\$url,array("classmap"=>self::\$classmap,"trace" => true,"exceptions" => true));
    }
    $methods
}
EOT;

        return $fullcode;
    }

    /**
     * Return an instance of the SOAP client.
     *
     * @return SoapClient
     */
    protected function getSoapClient()
    {
        if ( ! $this->soapClient ) {
            $this->soapClient = new SoapClient($this->url);
        }

        return $this->soapClient;
    }

    /**
     * Get types from SOAP response.
     *
     * @return array
     */
    protected function getTypes()
    {
        $soapClient = $this->getSoapClient();

        return $soapClient->__getTypes();
    }

    /**
     * Write the generated classes.
     *
     * @param array $types Types from SOAP response.
     *
     * @return string
     */
    protected function writeClasses(array $types)
    {
        $code = '';

        foreach ($types as $type) {
            if (substr($type, 0, 6) !== 'struct') {
                continue;
            }

            $data         = trim(str_replace(['{', '}'], '', substr($type, strpos($type, '{') + 1)));
            $data_members = explode(';', $data);
            $classname    = trim(substr($type, 6, strpos($type, '{') - 6));
            $this->classes[] = $classname;


            $code .= "\n" . 'class ' . $classname;
            $code .= "\n" . '{' . "\n";

            foreach ($data_members as $member) {
                $member = trim($member);

                if (strlen($member) < 1) {
                    continue;
                }

                list($data_type, $member_name) = explode(' ', $member);
                $code .= $this->tab . "protected \${$member_name}; // {$data_type}\n";
            }

            $code .= '}' . "\n";
        }

        return $code;
    }

    /**
     * Write the classmap.
     *
     * @return string
     */
    protected function writeMap() {
        $map = "\n" . $this->tab . 'private static $classmap = [';

        foreach ($this->classes as $class) {
            $map .= "\n" . $this->tab . $this->tab . "'$class'=>'$class',";
        }

        $map .= "\n" . $this->tab . '];' . "\n";

        return $map;
    }

    /**
     * Get functions from SOAP response.
     *
     * @return array
     */
    protected function getFunctions()
    {
        $soapClient = $this->getSoapClient();

        return $soapClient->__getFunctions();
    }

    /**
     * Write Service class methods.
     *
     * @param array $functions Functions from SOAP response.
     *
     * @return string
     */
    protected function writeMethods(array $functions)
    {
        $code = '';

        foreach ($functions as $func) {
            $temp = explode(' ', $func, 2);
            $t1   = str_replace(')', '', $temp[1]);
            $t1   = str_replace('(', ':', $t1);
            $t2   = explode(':', $t1);
            $func = $t2[0];
            $par  = $t2[1];
            $params = explode(' ', $par);
            $p1     = '$' . $params[0]; // Is this correct?!

            $code .= "\n" . $this->tab . 'public function ' . $func . '(' . $p1 . ')';
            $code .= "\n" . $this->tab . '{' . "\n";

            $return = 'void' == $temp[0] ? '' : 'return ';
            $code .= $this->tab . $this->tab . "{$return}\$this->soapClient->$func({$p1});";

            $code .= "\n" . $this->tab . '}' . "\n";
        }

        return $code;
    }
}
