<?php
namespace Provider;

class Template
{
    /**
     * 기본 디렉토리 주소
     * @var string
     */
    protected $dirPath;

    /**
     * 파일확장자
     * @var string
     */
    protected $fileExtension;

    /**
     * 해당 템플릿 파일 경로
     * @var string
     */
    protected $file;

    /**
     * 사용자 커스텀 data
     * @var array
     */
    protected $data = [];

    /**
     * extend 사용시 extend 정보가 담긴 배열
     * @var array
     */
    protected $extendSet;

    /**
     * start(), end()로 생성한 block들
     * @var array
     */
    protected $blocks = [];

    /**
     * @param string $dirPath
     * @param string $fileExtension
     */
    public function __construct($dirPath = '', $fileExtension = null)
    {
        $this->dirPath = rtrim($dirPath, '/\\');
        $this->fileExtension = $fileExtension;
    }

    /**
     * clone할 때 extendSet 제외하기.
     * 제거 하지 않을 경우, 카피된 인스턴스에 의해 같은 extend가 반복적으로 일어난다.
     */
    public function __clone()
    {
        $this->extendSet = null;
    }

    /**
     * 템플릿을 렌더링한다.
     * @param  string $file
     * @param  array  $data
     */
    public function render($file, $data = [])
    {
        $this->setFile($file);
        $this->setData($data);

        if (!is_file($this->file)) {
            throw new \InvalidArgumentException('Not Found File - ' . $this->file);
        }

        extract($this->data);

        ob_start();
        include $this->file;

        $contents = ob_get_clean();

        if (is_array($this->extendSet)) {

            list($exFile, $exData) = $this->extendSet;

            $extend = clone $this;
            $extend->render($exFile, $exData);
        } else {
            echo $contents;
        }
    }

    /**
     * @param string $file
     */
    protected function setFile($file)
    {
        $path = $this->dirPath . '/' . ltrim($file, '/\\');

        if ($this->fileExtension) {
            $path .= '.' . $this->fileExtension;
        }

        $this->file = $path;
    }

    /**
     * 사용자 커스텀 data를 이전 data(clone 전)와 merge.
     * @param array $data
     */
    protected function setData($data)
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * 다른 템플릿을 include한다.
     * @param  string $file
     * @param  array  $data
     */
    protected function insert($file, $data = [])
    {
        $template = clone $this;
        $template->render($file, $data = []);
    }

    /**
     * 상위 템플릿을 적용하기 위한 값을 속성에 저장한다.
     * @param  string $file
     * @param  array  $data
     */
    protected function extend($file, $data = [])
    {
        $this->extendSet = [$file, $data];
    }

    /**
     * @param  string $name
     * @return mixed
     */
    protected function block($name)
    {
        if (array_key_exists($name, $this->blocks)) {
            echo $this->blocks[$name];
        }
    }

    /**
     * start of block
     * @param  string $name
     */
    protected function start($name)
    {
        $self = $this;

        ob_start(function ($buffer) use ($self, $name) {
            $self->blocks[$name] = $buffer;
            return null;
        });
    }

    /**
     * end of block
     */
    protected function end()
    {
        ob_end_clean();
    }
}
