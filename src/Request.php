<?php

namespace VS\Request;

use VS\General\Singleton\{
    SingletonInterface, SingletonTrait
};

/**
 * Class Request
 * @package VS\Request
 * @author Varazdat Stepanyan
 */
class Request implements RequestInterface, SingletonInterface
{
    use SingletonTrait;

    /**
     * @var array $_bindParams
     */
    private $_bindParams = [];

    /**
     * @return string
     */
    public function method(): string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        return strtoupper($method);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return (bool)$this->get($key);
    }

    /**
     * @param array $data
     * @return void
     */
    public function bind(array $data): void
    {
        foreach ($data as $key => $value) {
            if (!is_numeric($key)) {
                $this->_bindParams[$key] = $value;
            }
        }
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $all = $this->all();
        return $all[$key] ?? null;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        $content = file_get_contents('php://input');
        $inputArray = [];

        if (strpos($content, 'WebKitFormBoundary') !== false) {
            $this->fillFromWebKitFormBoundary($content, $inputArray);
        } else {
            if ($content) {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'];
                if (in_array($contentType, ['application/json', 'application/javascript'])) {
                    $inputArray = json_decode($content, true);
                } else {
                    parse_str($content, $inputArray);
                }
            }
        }

        return array_merge($_GET, $inputArray, $this->_bindParams);
    }

    /**
     * @under development
     * @param string $content
     * @param array $inputArray
     */
    protected function fillFromWebKitFormBoundary(string $content, array &$inputArray)
    {
        preg_match_all("/form-data; name=(.*)\n(.*)\n(.*)/m", $content, $output);
        if (!empty($output[0])) {
            array_filter($output[0], function ($value) use (&$inputArray) {
                $value = preg_replace('/\s+/', '', $value);
                $partials = explode('"', $value, 3);
                if (!empty($partials[2])) {
                    $inputArray[$partials[1]] = $partials[2];
                }
            });
        }
    }
}