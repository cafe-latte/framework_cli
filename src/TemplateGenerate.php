<?php

namespace Cafelatte\PackageManager;


class TemplateGenerate
{

    private $permission;
    private $rsvWords;
    private $keyWords;
    private $compileDir;
    private $templateDir;
    private $fileUser;
    private $fileGroup;
    private $funcList;
    private $objList;
    private $split;
    private $index;
    private $expError;
    private $loopDepth;
    private $controlStack;
    private $loopInfo;
    private $loopStack;
    private $sizeInfo;
    private $outerSize;
    private $expLoopVar;
    private $inDiv;
    private $nlCnt;
    private $statement;
    private $nl;
    private $markPhp;
    private $_size_prefix;
    private $nlDel;
    private $funcPlugins;
    private $autoGlobals;
    private $objPlugins;
    private $expObject;
    private $compileExt;
    private $autoConstant;
    private $constants;
    private $quotedStr;
    private $allFunctions;


    /**
     * TemplateGenerator constructor.
     */
    public function __construct()
    {
        $this->permission = "0777";
        $this->compileExt = 'php';
        $this->autoConstant = empty($this->autoConstant) ? false : $this->autoConstant;
        $this->rsvWords = array('index_', 'size_', 'key_', 'value_');
        $this->keyWords = array('true', 'false', 'null');
        $this->constants = array_keys(get_defined_constants());
        $this->quotedStr = '(?:"(?:\\\\.|[^"])*")|(?:\'(?:\\\\.|[^\'])*\')';
        $this->allFunctions = \array_merge(array('isset', 'empty', 'eval', 'list', 'array', 'include', 'require', 'include_once', 'require_once'));
    }

    /**
     * @param $value
     */
    public function doValidationInput($value)
    {
        if (!$value) {
            ConsoleLog::doPrintMessage("red", "black", "Input Option MUSH be added", 2);
            exit;
        }
    }

    /**
     * @param $value
     */
    public function doValidationOutput($value)
    {
        if (!$value) {
            ConsoleLog::doPrintMessage("red", "black", "Output Option MUSH be added", 2);
            exit;
        }
    }

    /**
     * @param $params
     */
    public function doCompile($params)
    {
        $this->doValidationInput($params['template']['input']);
        $this->doValidationOutput($params['template']['output']);

        if ($params['template']['user']) {
            $user = $params['template']['user'];
        }

        if ($params['template']['group']) {
            $group = $params['template']['group'];
        }

        $this->doExecute($params['template']['input'], $params['template']['output'], $params['template']['input'], $user, $group);
    }

    /**
     * @param $params
     */
    public function doClean($params)
    {
        $this->doValidationInput($params['template']['input']);
        $this->doValidationOutput($params['template']['output']);

        $this->doTemplateClean($params['template']['output'], $params['template']['input']);
    }

    /**
     * @param string $compileDir
     */
    public function doDelete(string $compileDir)
    {
        foreach (scandir($compileDir) as $file) {
            if (($file == '.') || ($file == '..')) {
            } elseif (is_dir($compileDir . '/' . $file)) {
                $this->doDelete($compileDir . '/' . $file);
            } else {
                $path = pathinfo($compileDir . '/' . $file);
                if ($path['extension'] == 'php') {
                    $result = unlink($compileDir . '/' . $file);
                    if ($result == true) {
                        ConsoleLog::doPrintFile($compileDir . '/' . $file, "OK", "OK", "CLEAN");
                    } else {
                        ConsoleLog::doPrintFile($compileDir . '/' . $file, "OK", "ERR", "CLEAN");
                    }
                } else {
                    ConsoleLog::doPrintFile($compileDir . '/' . $file, "OK", "ERR", "CLEAN");
                }
            }
        }

        foreach (scandir($compileDir) as $file) {
            if (($file == '.') || ($file == '..')) {

            } elseif (is_dir($compileDir . '/' . $file)) {
                rmdir($compileDir . '/' . $file);
            }
        }
    }

    /**
     * @param string $compileDir
     * @param string $templateDir
     * @return mixed
     */
    public function doTemplateClean(string $compileDir, string $templateDir)
    {
        foreach (scandir($compileDir) as $file) {
            if (($file == '.') || ($file == '..')) {

            } elseif (is_dir($compileDir . '/' . $file)) {
                $this->doTemplateClean($compileDir . '/' . $file, $templateDir);
            } else {
                $path = pathinfo($compileDir . '/' . $file);
                if ($path['extension'] == 'php') {

                    $templateFile = str_replace(".php", "", $templateDir . '/' . $file);
                    if (file_exists($templateFile) == false) {
                        $result = unlink($compileDir . '/' . $file);
                        if ($result == true) {
                            ConsoleLog::doPrintFile($compileDir . '/' . $file, "OK", "OK", "DELETE");
                        } else {
                            ConsoleLog::doPrintFile($compileDir . '/' . $file, "OK", "ERR", "DELETE");
                        }
                    }
                } else {
                    ConsoleLog::doPrintFile($compileDir . '/' . $file, "OK", "ERR", "DELETE");
                }
            }
        }
    }

    /**
     * Do start to  convert from HTML to PHP, and Folders
     *
     * @param string $folder
     * @param string $compileDir
     * @param string $templateDir
     * @param string $fileUser
     * @param string $fileGroup
     */
    public function doExecute(string $folder, string $compileDir, string $templateDir, string $fileUser = "root", string $fileGroup = "root")
    {
        $this->compileDir = $compileDir;
        $this->templateDir = $templateDir;
        $this->fileUser = $fileUser;
        $this->fileGroup = $fileGroup;

        foreach (scandir($folder) as $file) {
            if (($file == '.') || ($file == '..')) {

            } elseif (is_dir($folder . '/' . $file)) {
                $this->doExecute($folder . '/' . $file, $compileDir, $templateDir, $fileUser, $fileGroup);
            } else {
                $fileName = str_replace($templateDir . "/", "", $folder . '/' . $file);
                $cplHead = '<?php /* ';
                $this->doGeneratePhpFile(realpath($templateDir . '/' . $fileName), $compileDir . '/' . $fileName, $cplHead);
            }
        }
    }


    /**
     * generate php file from html file
     *
     * @param string $tplPath
     * @param string $cplBase
     * @param string $cplHead
     * @return string
     */
    public function doGeneratePhpFile(string $tplPath, string $cplBase, string $cplHead)
    {
        $resultMessage = "OK";
        $cplPath = $cplBase . '.' . $this->compileExt;

        if (!is_file($cplPath)) {
            $dirs = explode('/', substr($cplPath, strlen($this->compileDir) + 1));
            $path = $this->compileDir;
            for ($i = 0, $s = count($dirs) - 1; $i < $s; $i++) {
                $path .= '/' . $dirs[$i];
                if (!is_dir($path)) {
                    if (false === mkdir($path, $this->permission)) {
                        $resultMessage = "Error #01 - cannot create compile directory";
                    }
                    exec("chmod -R " . $this->permission . " " . $path);
                    exec("chown -R " . $this->fileUser . "." . $this->fileGroup . " " . $path);
                }
            }
        }

        $source = '';
        if ($sourceSize = filesize($tplPath)) {
            $fp_tpl = @fopen($tplPath, 'rb');
            $source = fread($fp_tpl, $sourceSize);
            fclose($fp_tpl);
        }

        // remove UTF-8 BOM
        $source = \preg_replace('/^\xEF\xBB\xBF/', '', $source);

        $nl_cnt = 1;
        $nl_del_sum = 0;
        $this->nlDel[0] = 0;
        $nl = preg_match('/\r\n|\n|\r/', $source, $match) ? $match[0] : "\r\n";
        $escape_map = array('\\\\' => '\\', "\\'" => "'", '\\"' => '"', '\\n' => $nl, '\\t' => "\t", '\\>' => '>', '\\g' => '>');
        $split = preg_split('/({{\*|\*}}|{\*|\*})/', $source, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($j = 0, $i = 0, $s = count($split); $i < $s; $i++) {
            if (!($i % 2)) {
                $nl_cnt += \substr_count($split[$i], $nl);
                continue;
            }
            switch ($split[$i]) {
                case'{{*':
                case '{*':
                    if (\substr($split[$i + 1], 0, 1) == '\\')
                        $split[$i + 1] = \substr($split[$i + 1], 1);
                    elseif (!$j)
                        $j = $i;
                    break;
                case '*}}':
                case '*}' :
                    if (\substr($split[$i - 1], -1) === '\\')
                        $split[$i - 1] = \substr($split[$i - 1], 0, -1);
                    elseif ($j) {
                        if ($j === 1) {
                            for ($def_area = '', $k = 2; $k < $i; $k++)
                                $def_area .= $split[$k];
                            preg_match_all('@(?:(?:^|\r\n|\n|\r)[ \t]*)\#(prefilter|postfilter)[ \t]+(' . $this->quotedStr . '|(?:[^ \t\r\n]+))	@ix', $def_area, $match, PREG_PATTERN_ORDER);
                            for ($k = 0, $t = count($match[0]); $k < $t; $k++) {
                                if ($match[2][$k][0] === "'") {
                                    $f_string = strtr(\substr($match[2][$k], 1, -1), $escape_map);
                                } elseif ($match[2][$k][0] === '"') {
                                    $f_string = strtr(\substr($match[2][$k], 1, -1), $escape_map);
                                } else {
                                    $f_string = $match[2][$k];
                                }

                                if (!trim($f_string)) {
                                    $this->$match[1][$k] = '';
                                } else {
                                    $f_split = preg_split('@(?<!\\\\)\|@', $f_string);
                                    if (!trim($f_split[0])) {
                                        $this->$match[1][$k] .= $f_string;
                                    } elseif (!trim($f_split[count($f_split) - 1])) {
                                        $this->$match[1][$k] = $f_string . $this->$match[1][$k];
                                    } else {
                                        $this->$match[1][$k] = $f_string;
                                    }
                                }
                            }
                            preg_match_all('@(?:(?:^|\r\n|\n|\r)[ \t]*)\#define[ \t]+(' . $this->quotedStr . '|(?:\S+))[ \t]+(' . $this->quotedStr . '|(?:\S+))	@ix', $def_area, $match, PREG_PATTERN_ORDER);
                            for ($k = 0, $t = count($match[0]); $k < $t; $k++) {
                                if ($match[1][$k][0] === "'") {
                                    $key = strtr(\substr($match[1][$k], 1, -1), $escape_map);
                                } elseif ($match[1][$k][0] === '"') {
                                    $key = strtr(\substr($match[1][$k], 1, -1), $escape_map);
                                } else {
                                    $key = strtr($match[1][$k], $escape_map);
                                }

                                if ($match[2][$k][0] === "'") {
                                    $val = strtr(\substr($match[2][$k], 1, -1), $escape_map);
                                } elseif ($match[2][$k][0] === '"') {
                                    $val = strtr(\substr($match[2][$k], 1, -1), $escape_map);
                                } else {
                                    $val = strtr($match[2][$k], $escape_map);
                                }
                                $macro[$key] = $val;
                            }
                        }
                        for ($nl_sub_cnt = 0, $k = $j; $k <= $i; $k++) {
                            $nl_sub_cnt += \substr_count($split[$k], $nl);
                            $split[$k] = '';
                        }
                        $split[$j - 1] = \preg_replace('/(^|\r\n|\n|\r)[ \t]*$/', '$1', $split[$j - 1]);
                        if (preg_match('/^[ \t]*(\r\n|\n|\r)/', $split[$i + 1])) {
                            $nl_del_sum++;
                            $split[$i + 1] = \preg_replace('/^[ \t]*(\r\n|\n|\r)/', '', $split[$i + 1]);
                        }
                        $nl_del_sum += $nl_sub_cnt;
                        $nl_cnt -= $nl_sub_cnt;
                        $this->nlDel[$nl_cnt] = $nl_del_sum;
                        $j = 0;
                    }
            }
        }
        krsort($this->nlDel);
        $source = implode('', $split);

        if (!empty($macro)) {
            $source = strtr($source, $macro);
        }

        $this->controlStack = array();
        $this->loopDepth = 0;
        $this->loopStack = array();
        $this->loopInfo = array();
        $this->sizeInfo = array();
        $this->_size_prefix = '';
        $this->inDiv = '';
        $this->nlCnt = 1;
        $this->nl = preg_match('/\r\n|\n|\r/', $source, $match) ? $match[0] : "\r\n";

        $division = array();
        $divNames = array();
        $nl = $this->nl;

        $phpTag = '<\?php|(?<!`)\?>';
        $phpTag .= '|';

        $this->split = preg_split('/(' . $phpTag . '{{(?!`)|(?<!`)}}|{(?!`)|(?<!`)})/i', $source, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($this->markPhp = 0, $mark_tpl = 0, $this->index = 0, $s = count($this->split); $this->index < $s; $this->index++) {
            if (!($this->index % 2)) {
                $this->nlCnt += \substr_count($this->split[$this->index], $nl);
                continue;
            }
            switch (strtolower($this->split[$this->index])) {
                case'<?php':
                case'?>':
                case'{{':
                case'{':
                    $mark_tpl = $this->index;
                    break;
                case '}}':
                case '}' :
                    if ($mark_tpl !== $this->index - 2) {
                        break;
                    }
                    if (!$result = $this->compileStatement($this->split[$this->index - 1])) {
                        break;
                    }
                    if (is_array($result)) {
                        if ($this->markPhp) {
                            if ($result[0] === 1) {
                                $this->split[$this->index - 1] = \substr($result[1], 4);
                                $this->split[$mark_tpl] = '';
                                $this->split[$this->index] = '';
                            } else {
                                $resultMessage = "Error #02 - template control statement " . $this->statement . "in php code is not available";
                            }
                        } elseif ($result[0] === 8) {
                            if ($result[1]) {
                                if (in_array($result[1], $divNames)) {
                                    $resultMessage = "Error #03 - template division id  " . $result[1] . " is overlapped";
                                }
                                $divNames[] = $result[1];
                                $num = count($division);
                                $division[$num] = array('name' => $result[1], 'start' => $this->index - 1);
                                if ($num && !isset($division[--$num]['end'])) {
                                    $division[$num]['end'] = $this->index - 1;
                                }
                                $this->inDiv = $result[1];
                                $this->funcList[$result[1]] = array();
                                $this->objList[$result[1]] = array();
                            } elseif ($num = count($division) and !isset($division[--$num]['end'])) {
                                $division[$num]['end'] = $this->index - 1;
                                $this->inDiv = '';
                            }
                            $this->split[$mark_tpl - 1] = \preg_replace('/\s*$/', '', $this->split[$mark_tpl - 1]);
                            if (preg_match('/^\s*/', $this->split[$this->index + 1], $match)) {
                                $this->nlCnt += (\substr_count($match[0], $nl) - 1);
                                $this->split[$this->index + 1] = \preg_replace('/^\s*/', $nl, $this->split[$this->index + 1]);
                            }
                            $this->split[$this->index - 1] = '';
                            $this->split[$mark_tpl] = '';
                            $this->split[$this->index] = '';
                        } elseif ($result[0] === 16) {
                            $this->split[$this->index - 1] = $result[1];
                        } else {
                            if ($result[0] & 6)
                                $this->split[$mark_tpl - 1] = \preg_replace('/(\r\n|\n|\r)[ \t]+$/', '$1', $this->split[$mark_tpl - 1]);
                            if ($result[0] & 5 and preg_match('/^[ \t]*(\r\n|\n|\r)/', $this->split[$this->index + 1])) {
                                $this->nlCnt--;
                                $this->split[$this->index + 1] = \preg_replace('/^[ \t]*(\r\n|\n|\r)/', '$1$1', $this->split[$this->index + 1]);
                            }
                            if ($this->_size_prefix) {
                                $result[1] = $this->_size_prefix . $result[1];
                                $this->_size_prefix = '';
                            }
                            $this->split[$this->index - 1] = '<?php ' . $result[1] . '?>';
                            $this->split[$mark_tpl] = '';
                            $this->split[$this->index] = '';
                        }
                    } elseif ($result === -1) {
                        $erlist[] = array(htmlspecialchars($this->split[$this->index - 1]), $this->nlCnt);
                    } elseif ($result === -2 || $result === -3) {
                        if ($result === -2) {
                            $resultMessage = "Error - unexpected directive";
                        } elseif ($result === -3) {
                            $resultMessage = "Error - unexpected directive";
                        }
                        if (!empty($erlist)) {
                            foreach ($erlist as $er) {
                                $resultMessage = "Error - " . $er[0] . "may be syntax error";
                            }
                        }
                    }
            }
        }

        if (!empty($this->controlStack)) {
            $resultMessage = "Error - template loop or branch is not properly closed by";
        }


        if ($resultMessage == 'OK') {
            $source = trim(implode('', $this->split));
            $size_of_top_loop = empty($this->sizeInfo[1]) ? '' : $this->getLoopSize(1);
            $resultMessage = $this->doMakeAndSavePhpFile($cplPath, $cplHead, ' */ ' . $size_of_top_loop . '?>' . $nl, $source);

            ConsoleLog::doPrintFile($cplBase, $resultMessage, "OK", "CREATE");
        } else {
            ConsoleLog::doPrintFile($cplBase, $resultMessage, "FAIL", "CREATE");

        }
    }

    /**
     * make php file and store source code into it.
     *
     * @param string $cplPath pHP Full Path
     * @param string $cplHead pHP header comment message
     * @param string $initCode pHP initCode
     * @param string $source PHP Source Code from html after converting
     * @return string
     */
    private function doMakeAndSavePhpFile(string $cplPath, string $cplHead, string $initCode, string $source)
    {
        $sourceSize = strlen($cplHead) + strlen($initCode) + strlen($source) + 9;
        $source = $cplHead . str_pad($sourceSize, 9, '0', STR_PAD_LEFT) . $initCode . $source;

        $fpCpl = @fopen($cplPath, 'wb');
        if (false === $fpCpl) {
            return "\e[31 Error - cannot write compiled file";
        }
        fwrite($fpCpl, $source);
        fclose($fpCpl);

        if (filesize($cplPath) != strlen($source)) {
            unlink($cplPath);
            return "\e[31m Error - Problem by concurrent access";
        }

        exec("chmod " . $this->permission . " " . $cplPath);
        exec("chown -R " . $this->fileUser . "." . $this->fileGroup . " " . $cplPath);
        return "\e[32m OK";
    }

    /**
     * @param $statement
     * @return array|int
     */
    private function compileStatement($statement)
    {
        $match = array();
        preg_match('/^(\\\\*)\s*(:\?|[=#@?:\/+])?(.*)$/s', $statement, $match);
        $src = preg_split('/(' . $this->quotedStr . ')/', $match[3], -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0; $i < count($src); $i += 2) {
            if (($comment = strpos($src[$i], '//')) !== false) {
                $src[$i] = \substr($src[$i], 0, $comment);
                break;
            }
        }
        $src = trim(implode('', array_slice($src, 0, $i + 1)));
        $this->statement = htmlspecialchars($statement);
        if ($match[1]) {
            switch ($match[2]) {
                case '#':
                    return preg_match('/^[A-Z_a-z\x7f-\xff][\w\x7f-\xff]*$/', $src) ? array(16, \substr($statement, 1)) : 0;
                case '+':
                    return !strlen($src) || preg_match('/^[A-Z_a-z\x7f-\xff][\w\x7f-\xff]*$/', $src) ? array(16, \substr($statement, 1)) : 0;
                case '/':
                    return !strlen($src) ? array(16, \substr($statement, 1)) : 0;
                case '?':
                    return $this->compileBranch($src, 1, 0) !== 0 ? array(16, \substr($statement, 1)) : 0;
                case ':':
                    return !strlen($src) || $this->compileBranch($src, 1, 1) !== 0 ? array(16, \substr($statement, 1)) : 0;
                case '' :
                    return $this->compileExpression($src, 1, 1) !== 0 ? array(16, \substr($statement, 1)) : 0;
                default :
                    return $this->compileExpression($src, 1, 0) !== 0 ? array(16, \substr($statement, 1)) : 0; // = @ :?
            }
        }
        switch ($match[2]) {
            case '' :
                return (($xpr = $this->compileExpression($src, 0, 1)) === 0) ? 0 : array(1, 'echo ' . $xpr);
            case '=' :
                return (($xpr = $this->compileExpression($src, 0, 0)) === 0) ? 0 : array(1, 'echo ' . $xpr);
            case ':?':
                return (($xpr = $this->compileExpression($src, 0, 0)) === 0) ? 0 : array(2, '}elseif(' . $xpr . '){'); // deprecated
            case '+' :
                return !strlen($src) || preg_match('/^[A-Z_a-z\x7f-\xff][\w\x7f-\xff]*$/', $src) ? array(8, $src) : 0;
            case '#' :
                return preg_match('/^[A-Z_a-z\x7f-\xff][\w\x7f-\xff]*$/', $src) ? array(4, '$this->execute("' . $src . '",$TPL_SCP,1);') : 0;
            case '@' :
                $xpr = preg_match('/^[A-Z_a-z\x7f-\xff][\w\x7f-\xff]*$/', $src) ? '' : $this->compileExpression($src, 0, 0);
                if ($xpr === 0) {
                    return -1;
                }
                $d = ++$this->loopDepth;
                $this->controlStack[] = '@';
                $this->loopInfo[$d] = array('index' => $this->index - 1, 'foreach_bit' => 0);
                if ($xpr) {
                    $this->loopStack[] = '*';
                    return array(2, 'if(is_array($TPL_R' . $d . '=' . $xpr . ')&&!empty($TPL_R' . $d . ')){');
                }
                if ($d > 1 && in_array($src, $this->loopStack)) {
                    return "Error #08 - id of nested loop " . $src . "in" . $this->statement . " cannot be same as parent loop id";
                }
                $this->sizeInfo[$d][$src] = 1;
                if ($d === 1 && $this->inDiv) {
                    $this->sizeInfo[$this->inDiv][$src] = 1;
                }
                $this->loopStack[] = $src;
                $this->loopInfo[$src] = $d;
                return array(2, 'if($TPL_' . $src . '_' . $d . '){');
            case '?' :
                if (($stt = $this->compileBranch($src, 0, 0)) === 0)
                    return -1;
                $this->controlStack[] = \substr($stt, 0, 2) === 'if' ? '?' : '$';
                return array(2, $stt);
            case ':' :
                if (strlen($src)) {
                    if (($stt = $this->compileBranch($src, 0, 1)) === 0)
                        return 0;
                    if (empty($this->controlStack))
                        return -3;
                    switch (array_pop($this->controlStack)) {
                        case '?':
                            if (($xpr = $this->compileExpression($src, 0, 0)) === 0)
                                return 0;
                            $this->controlStack[] = '?';
                            return array(2, '}elseif(' . $xpr . '){');
                        case '$':
                            $this->controlStack[] = '$';
                            return array(2, 'break;' . $stt);
                        case 'else':
                            return "Error #09 - elseif statement" . $this->statement . " after else statement {:} is not available";
                        case 'default':
                            return "Error #10 - case statement" . $this->statement . " after else statement {:} is not available";
                        case 'loopelse':
                            return "Error #11 - elseif statement" . $this->statement . " after loopelse statement {:} is not available";
                        default : // loop
                            return "Error #12 - " . $this->statement . " is not in proper position";
                    }
                } else {
                    if (empty($this->controlStack))
                        return -3;
                    switch (array_pop($this->controlStack)) {
                        case '?':
                            $this->controlStack[] = 'else';
                            return array(2, '}else{');
                        case '$':
                            $this->controlStack[] = 'default';
                            return array(2, 'break;default:');
                        case 'else':
                            return "Error #13 - elseif statement" . $this->statement . " after else statement {:} is not available";
                        case 'default':
                            return "Error #14 - default statement" . $this->statement . " after default statement {:} is not available";
                        case 'loopelse':
                            return "Error #15 - else statement" . $this->statement . " after loopelse statement {:} is not available";
                        default : // loop
                            $this->closeLoop();
                            $this->controlStack[] = 'loopelse';
                            return array(2, '}}else{');
                    }
                }
            case '/' :
                if (strlen($src)) {
                    return 0;
                }
                if (empty($this->controlStack)) {
                    return -2;
                }
                if ('@' === array_pop($this->controlStack)) {
                    $this->closeLoop();
                    return array(2, '}}');
                }
                return array(2, '}');
        }

        return null;
    }

    /**
     * @param $source
     * @param int $escape
     * @param int $case
     * @return int|string
     */
    private function compileBranch($source, $escape = 0, $case = 0)
    {
        $expression = $source;
        $casePos = false;
        $split = preg_split('/(' . $this->quotedStr . ')/', $source, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0; $i < count($split); $i += 2) {
            if (($casePos = strpos($split[$i], ':')) !== false)
                break;
        }
        if ($casePos !== false) {
            $expression = trim(implode('', array_slice($split, 0, $i))) . \substr($split[$i], 0, $casePos);
        }
        $xpr = $this->compileExpression($expression, $escape, 0);
        if ($xpr === 0)
            return 0;
        if ($escape) {
            return 1;
        }
        return $case ? 'case ' . $xpr . ':' : 'if(' . $xpr . '){';
    }

    /**
     *
     */
    private function closeLoop()
    {
        $loopId = array_pop($this->loopStack);
        $depth = $this->loopDepth--;
        $info = &$this->loopInfo[$depth];

        // 1: key_, 2: value_, 4: index_, 8: size_
        $_key = $info['foreach_bit'] & 1 ? '$TPL_K' . $depth . '=>' : '';
        if ($info['foreach_bit'] & 4) {
            $_idx1 = '$TPL_I' . $depth . '=-1;';
            $_idx2 = '$TPL_I' . $depth . '++;';
        } else {
            $_idx1 = '';
            $_idx2 = '';
        }
        $_sub_loop_size = empty($this->sizeInfo[$depth + 1]) ? '' : $this->getLoopSize($depth + 1);
        $split = &$this->split[$info['index']];
        $split = \substr($split, 0, -2);
        if ($loopId === '*') {
            $_size = $info['foreach_bit'] & 8 ? '$TPL_S' . $depth . '=count($TPL_R' . $depth . ');' : '';
            $split .= $_size . $_idx1 . 'foreach($TPL_R' . $depth . ' as ' . $_key . '$TPL_V' . $depth . '){' . $_idx2 . $_sub_loop_size . '?>';
        } else {
            $split .= $_idx1 . 'foreach(' . $this->getLoopArray($loopId, $depth) . ' as ' . $_key . '$TPL_V' . $depth . '){' . $_idx2 . $_sub_loop_size . '?>';
        }
        unset($this->sizeInfo[$depth + 1], $this->loopInfo[$depth], $this->loopInfo[$loopId]);
    }

    /**
     * @param $depth
     * @param string $div
     * @return string
     */
    private function getLoopSize($depth, $div = '')
    {
        $size = '';
        $array = $div ? $this->sizeInfo[$div] : $this->sizeInfo[$depth];
        foreach ($array as $loopId => $val) {
            $loop_array = $this->getLoopArray($loopId, $depth);
            $size .= $this->nl . '$TPL_' . $loopId . '_' . $depth . '=empty(' . $loop_array . ')||!is_array(' . $loop_array . ')?0:count(' . $loop_array . ');';
        }
        return $size;
    }

    /**
     * @param $loopId
     * @param $depth
     * @return string
     */
    private function getLoopArray($loopId, $depth)
    {
        if ($depth === 1) {
            if ($loopId[0] === '_') {
                return in_array($loopId, $this->autoGlobals) ? '$' . $loopId : '$GLOBALS["' . \substr($loopId, 1) . '"]';
            }
            return '$tplVar["' . $loopId . '"]';
        }
        return '$TPL_V' . ($depth - 1) . '["' . $loopId . '"]';
    }

    /**
     * @param $expression
     * @param int $escape
     * @param int $noDirective
     * @return int|string
     */
    private function compileExpression($expression, $escape = 0, $noDirective = 0)
    {
        if (!strlen($expression))
            return 0;
        $varState = array(0, '');     // 0:
        $parStack = array();
        $funcList = array();
        $this->expObject = array();
        $this->expError = array();
        $this->expLoopVar = array();
        $this->outerSize = array();
        $numberUsed = 0;
        $prevIsOperand = 0;
        $prevIsFunc = 0;
        $m = array();
        for ($xpr = '', $i = 0; strlen($expression); $expression = \substr($expression, strlen($m[0])), $i++) { //
            if (!preg_match('/^
				((?:\.\s*)+)
				|(?:([A-Z_a-z\x7f-\xff][\w\x7f-\xff]*)\s*(\[|\.|\(|\-\>)?)
				|(?:(\])\s*(\-\>|\.|\[)?)
				|((?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+\-]?\d+)?)
				|(' . $this->quotedStr . ')
				|(===|!==|\+\+|--|\+\.|<<|>>|<=|>=|==|!=|&&|\|\||[,+\-*\/%&^~|<>()!])
				|(\s+)
				|(.+)
			/ix', $expression, $m))
                return 0;
            if (!empty($m[10])) { // (.+)
                return 0;
            } elseif ($m[1]) {  // ((?:\.\s*)+)
                if ($prevIsOperand || $varState[0])
                    return 0;
                $prevIsOperand = 1;
                $varState = array(1, \preg_replace('/\s+/', '', $m[1]));
            } elseif ($m[2]) {  // ([A-Z_a-z\x7f-\xff][\w\x7f-\xff]*)
                if (empty($m[3]))
                    $m[3] = '';  // (\[|\.|\(|\-\>)
                switch ($m[3]) {
                    case '' :
                        switch ($varState[0]) {
                            case 0:
                                if ($prevIsOperand)
                                    return 0;
                                $prevIsOperand = 1;
                                if (in_array(strtolower($m[2]), $this->keyWords) || $this->autoConstant && in_array($m[2], $this->constants)) {
                                    $xpr .= $m[2];
                                } elseif ($m[2] === 'this') {
                                    $xpr .= '$this';
                                } elseif ($m[2] === 'tpl_') {
                                    $xpr .= '$TPL_TPL';
                                } elseif ($m[2][0] === '_') {
                                    $xpr .= in_array($m[2], $this->autoGlobals) ? '$' . $m[2] : '$GLOBALS["' . \substr($m[2], 1) . '"]';
                                } else {
                                    $xpr .= '$tplVar["' . $m[2] . '"]';
                                }
                                break;
                            case 1:
                                $xpr .= $this->compileArray($varState[1] . $m[2], 'stop');
                                break;
                            case 2:
                                $xpr .= $varState[1] === 'obj' ? $m[2] : '["' . $m[2] . '"]';
                                break;
                        }
                        $varState = array(0, '');
                        break;
                    case '(' :
                        if ($varState[0]) {
                            if ($varState[1] !== 'obj')
                                return 0;
                        } else {
                            if ($noDirective)
                                return 0;
                            $func = strtolower($m[2]);
                            if (in_array($func, $this->funcPlugins)) {
                                $funcList[$func] = $this->nlCnt;
                            } else {
                                in_array($func, $this->allFunctions) or $this->expError[] = array(7, $m[2]);
                            }
                        }
                        $prevIsOperand = 0;
                        $prevIsFunc = 1;
                        $parStack[] = 'f';
                        $varState = array(0, '');
                        $xpr .= $m[2] . '(';
                        break;
                    case '[' :
                        switch ($varState[0]) {
                            case 0:
                                if ($prevIsOperand)
                                    return 0;
                                $xpr .= $this->compileArray($m[2]) . '[';
                                break;
                            case 1:
                                $xpr .= $this->compileArray($varState[1] . $m[2]) . '[';
                                break;
                            case 2:
                                $xpr .= $varState[1] === 'obj' ? $m[2] . '[' : '["' . $m[2] . '"][';
                                break;
                        }
                        $parStack[] = '[';
                        $prevIsOperand = 0;
                        $prevIsFunc = 0;
                        $varState = array(0, '');
                        break;
                    case '.' :
                        switch ($varState[0]) {
                            case 0:
                                if ($prevIsOperand)
                                    return 0;
                                $prevIsOperand = 1;
                                $varState = array(1, $m[2] . '.');
                                break;
                            case 1:
                                $xpr .= $this->compileArray($varState[1] . $m[2]);
                                $varState = array(2, '');
                                break;
                            case 2:
                                $xpr .= $varState[1] === 'obj' ? $m[2] : '["' . $m[2] . '"]';
                                break;
                        }
                        break;
                    case '->':
                        switch ($varState[0]) {
                            case 0:
                                if ($prevIsOperand)
                                    return 0;
                                $prevIsOperand = 1;
                                if (in_array($m[2], $this->loopStack)) {
                                    $xpr .= '$TPL_V' . $this->loopInfo[$m[2]] . '->';
                                } elseif ($m[2] === 'this') {
                                    $xpr .= '$this->';
                                } elseif ($m[2][0] === '_') {
                                    $xpr .= '$GLOBALS["' . \substr($m[2], 1) . '"]->';
                                } else {
                                    $xpr .= '$tplVar["' . $m[2] . '"]->';
                                }
                                break;
                            case 1:
                                $xpr .= $this->compileArray($varState[1] . $m[2], 'obj') . '->';
                                break;
                            case 2:
                                $xpr .= ($varState[1] === 'obj' ? $m[2] : '["' . $m[2] . '"]') . '->';
                                break;
                        }
                        $varState = array(2, 'obj');
                        break;
                }
            } elseif ($m[4]) { //	(\])
                if ($varState[0] || !$prevIsOperand || empty($parStack) || array_pop($parStack) !== '[')
                    return 0;
                if (empty($m[5]))
                    $m[5] = '';
                switch ($m[5]) {
                    case '' :
                        $xpr .= ']';
                        break;
                    case '->':
                        $xpr .= ']->';
                        $varState = array(2, 'obj');
                        break;
                    case '.' :
                        $xpr .= ']';
                        $varState = array(2, '');
                        break;
                    case '[' :
                        $xpr .= '][';
                        $parStack[] = '[';
                        $prevIsOperand = 0;
                        $prevIsFunc = 0;
                        break;
                }
            } elseif ($m[6] || $m[6] === '0') {   // ((?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+\-]?\d+)?)
                if ($prevIsOperand)
                    return 0;
                $xpr .= ' ' . $m[6];
                $prevIsOperand = 1;
                $numberUsed = 1;
            } elseif ($m[7]) {
                if ($prevIsOperand || preg_match('/ [+\-]$/', $xpr))
                    return 0;
                $xpr = \preg_replace('/\+$/', '.', $xpr) . strtr($m[7], array('``' => '`', '{`' => '{', '`}' => '}', '<?`' => '<?', '`?>' => '?>', '<%`' => '<%', '`%>' => '%>'));
                $prevIsOperand = 1;
            } elseif ($m[8]) {
                if ($varState[0])
                    return 0;
                switch ($m[8]) {
                    case'++':
                    case'--':
                        return 0;
                    case ',':
                        if (!$prevIsOperand || empty($parStack) || $parStack[count($parStack) - 1] !== 'f')
                            return 0;
                        $prevIsOperand = 0;
                        break;
                    case '(':
                        if ($prevIsOperand)
                            return 0;
                        $parStack[] = 'p';
                        break;
                    case ')':
                        if (!$prevIsOperand && !$prevIsFunc || empty($parStack) || array_pop($parStack) === '[')
                            return 0;
                        $prevIsOperand = 1;
                        break;
                    case '!':
                    case '~':
                        if ($prevIsOperand)
                            return 0;
                        break;
                    case '-':
                        if ($prevIsOperand)
                            $prevIsOperand = 0;
                        else
                            $m[8] = ' -';
                        break;
                    case '+':
                        if (preg_match('/["\']$/', $xpr)) {
                            $m[8] = '.';
                            $prevIsOperand = 0;
                        } else {
                            if ($prevIsOperand)
                                $prevIsOperand = 0;
                            else
                                $m[8] = ' +';
                        }
                        break;
                    case '+.':
                        $m[8] = '.';
                        break;
                    default :
                        if (!$prevIsOperand)
                            return 0;
                        $prevIsOperand = 0;
                }
                $xpr .= $m[8];
                $prevIsFunc = 0;
            } else {
                continue;
            }
        }
        if (!empty($parStack) || !$prevIsOperand || $varState[0] || $noDirective && $i === 1 && $numberUsed) {
            return 0;
        }

        if ($escape) {
            return 1;
        }


        foreach ($this->outerSize as $loopId => $depth) {
            if ($depth === 1 && $this->inDiv) {
                $this->sizeInfo[$this->inDiv][$loopId] = 1;
            }
            if (empty($this->sizeInfo[$depth][$loopId])) {
                $this->sizeInfo[$depth][$loopId] = array($this->statement, $this->nlCnt);
            }
        }

        foreach ($this->expLoopVar as $depth => $set) {
            $this->loopInfo[$depth]['foreach_bit'] |= $set;
        }


        return $xpr;
    }


    /**
     * @param $subject
     * @param string $end
     * @return string
     */
    private function compileArray($subject, $end = '')
    {
        if (preg_match('/^\.+/', $subject, $match)) { // ..loop
            $depth = strlen($match[0]);
            if ($this->loopDepth < $depth) {
                $this->expError[] = array(3, $subject);
                return '';
            }
            $id = $this->loopStack[$depth - 1];
            $var = \substr($subject, $depth);
            $el = '["' . $var . '"]';
        } else {
            if ($D = strpos($subject, '.')) { // id.var
                $id = \substr($subject, 0, $D);
                $var = \substr($subject, $D + 1);
                $el = '["' . $var . '"]';
                if ($id === 'p' || $id === 'P') { // p.object
                    if (!$end) {
                        $this->expError[] = array(1, $subject);
                        return '';
                    }
                    $obj = strtolower($var);
                    if (in_array($obj, $this->objPlugins)) {
                        $this->expObject[$obj] = $this->nlCnt;
                    } else {
                        $this->expError[] = array(8, $subject);
                    }
                    return '$TPL_' . $obj . '_OBJ';
                } elseif ($id === 'c' || $id === 'C') { // c.constant
                    if ($end !== 'stop') {
                        $this->expError[] = array(2, $subject);
                    }
                    return $var;
                } elseif (in_array($id, $this->loopStack)) { // loop.var
                    $depth = $this->loopInfo[$id];
                } elseif ($var === 'size_') { // outside.size_
                    if ($end !== 'stop') {
                        $this->expError[] = array(-1, $subject);
                    }
                    $depth = $this->loopDepth + 1;
                    $this->outerSize[$id] = $depth;
                    return '$TPL_' . $id . '_' . $depth;
                } elseif (in_array($var, $this->rsvWords)) { // array.key_ , value_ , index_
                    $this->expError[] = array(3, $subject);
                    return '';
                }
            } else { // id[
                $id = $subject;
                $var = '';
                $el = '';
                if (in_array($id, $this->loopStack)) {
                    $depth = $this->loopInfo[$id];
                }
            }
            if (empty($depth)) { // not loop
                if ($id[0] === '_') {
                    if (in_array($id, $this->autoGlobals)) {
                        return '$' . $id . $el;
                    }
                    return '$GLOBALS["' . \substr($id, 1) . '"]' . $el;
                }
                return '$tplVar["' . $id . '"]' . $el;
            }
        }
        switch ($var) {
            case 'key_':
                if ($end !== 'stop') {
                    $this->expError[] = array(-1, $subject);
                } elseif (isset($this->expLoopVar[$depth])) {
                    $this->expLoopVar[$depth] |= 1;
                } else {
                    $this->expLoopVar[$depth] = 1;
                }
                return '$TPL_K' . $depth;
            case 'value_':
                if (isset($this->expLoopVar[$depth])) {
                    $this->expLoopVar[$depth] |= 2;
                } else {
                    $this->expLoopVar[$depth] = 2;
                }
                return '$TPL_V' . $depth;
            case 'index_':
                if ($end !== 'stop') {
                    $this->expError[] = array(-1, $subject);
                } elseif (isset($this->expLoopVar[$depth])) {
                    $this->expLoopVar[$depth] |= 4;
                } else {
                    $this->expLoopVar[$depth] = 4;
                }
                return '$TPL_I' . $depth;
            case 'size_':
                if ($end !== 'stop') {
                    $this->expError[] = array(-1, $subject);
                } elseif (isset($this->expLoopVar[$depth])) {
                    $this->expLoopVar[$depth] |= 8;
                } else {
                    $this->expLoopVar[$depth] = 8;
                }
                return $id === '*' ? '$TPL_S' . $depth : '$TPL_' . $id . '_' . $depth;
            default :
                return '$TPL_V' . $depth . $el;
        }
    }


}