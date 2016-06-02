CHOMini
===
Ultra Very Too Cho(超) Mini Simple PHP FrameWork

## Intro
지금 내가 하는 일엔 micro 프레임웍조차 필요없다고!
> 가장 기본적인 것, 가장 빠른 방법으로...

Usage
---
### Router
`route.php`에 URL과 콜백을 등록합니다.
```php
$router->add('/', function () {
    echo 'hello world';
});
```
아! add만 있습니다. 어차피 우리가 하는 일엔 *POST*와 *GET*을 구분할 필요가 없잖아요?
##### with Parameter
파라미터를 사용할 수 있습니다. 정규표현식을 사용하세요.
```php
$router->add('/bbs/(\w+)/(\d+)', function ($category, $id) {
    echo $category;
    echo $id;
});
```
##### with Controller
콜백함수(Closure)뿐만 아니라 클래스 메소드도 등록할 수도 있습니다.
```php
$router->add('/, 'Controller\main@index');
```
`Controller/main.php`을 작성합니다. 반드시 `Controller\BaseController`을 상속받아야 합니다.
```php
<?php
namespace Controller;

class main extends BaseController
{
    public function index()
    {
        echo 'hi!';
    }
}
````

### DI Container
`Provider/Container.php`을 열어 컨테이너에 담을 인스턴스를 정의합니다. 메소드에 단지 set을 붙여주시면 됩니다.
```php
    protected function setPdo()
    {
        $pdo = new PDO("mysql:host=localhost;dbname=test", 'test', '1234');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        return $pdo;
    }
```
```php
$router->add('/', function () {
    $pdo = $this->container->pdo;
});
```
이제 콜백함수(Closure)와 컨트롤러에서 $this->container의 속성으로 불러올 수 있습니다.
컨트롤러 생성자단계에선 컨테이너를 인자로 받을 수 있습니다.
```php
<?php
namespace Controller;

class main extends BaseController
{
    public function __construct($container)
    {
        $pdo = $container->pdo;
    }
````
컨테이너를 이용하면 복잡한 의존관계를 간편하게 정의할 수 있습니다.
```php
    protected function setGrand()
    {
        return new Grand;
    }

    protected function setParent()
    {
        return new Parent($this->grand);
    }

    protected function setChild()
    {
        return new Child($this->grand, $this->parent);
    }
```

### View
PHP는 템플릿엔진이 따로 필요없을 정도로 템플릿 친화적인 숏태그가 이미 존재합니다. 그러나 템플릿 엔진의 상속기능은 너무 탐이 납니다!
```php
$router->add('/', function () {
    $this->container->view->render('main.php');
});
```
`Templates/main.php`
```html
<?php $this->extend('layout.php')?>

<?php $this->start('contents')?>
   <h1>Hi</h1>
   <section>hello world!</section>
<?php $this->end()?>
```
`Templates/layout.php`
```html
<html>
<head>
  <title>CHOMini</title>
</head>
<body>
    <?php $this->block('contents')?>
</body>
</html>
```
상속기능만 가져온 건 아닙니다. 당연히 변수를 할당할 수도 있습니다.
```php
$router->add('/', function () {
    $this->container->view->render('main.php', ['simple'=>'심플!']);
});
```
`Templates/main.php`
```php
<?=$simple?>입니다!
```
output : 심플!입니다!

### Entity(ORM)
간단한 테이블 CRUD뿐이라면 눈에 안들어오는 쿼리조작을 할 필요가 있을까요?
테이블을 생성합니다.
```sql
CREATE TABLE `Post` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(100) NOT NULL,
    `body` TEXT NOT NULL,
    `create_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COLLATE='utf8_general_ci';
```


`Entities/Post.php`을 만듭니다. 클래스명(Post)과 테이블명은 일치해야 합니다. 또 반드시 `Entities\AbstractEntity`을 상속 받아야 합니다.
```php
<?php
namespace Entities;
class Post extends AbstractEntity
{
}
```

#### Create
```php
$router->add('/', function () {
    $newPost = $this->container->entityFactory->create('Post', [
        'title' => '제목!',
        'body'  => ' 내용!!',
    ]);
});
```
#### Read
```php
$router->add('/', function () {
    $post = $this->container->entityFactory->findOne('Post', ['id'=>1]);
    echo $post->id;    // output : 1
    echo $post->title; // output : 제목!
});
```
`findOne()`과 `find()`메서드가 있습니다. `find()`는 일치하는 결과 *모두*를 배열로 반환받습니다.

#### Update
```php
$router->add('/', function () {
    $post = $this->container->entityFactory->findOne('Post', ['id'=>1]);
    $post->title = '제목변경!';
    $post->save();
});
```
`save()`메소드를 사용하면 DB에 업데이트합니다.

#### Delete
```php
$router->add('/', function () {
    $post = $this->container->entityFactory->findOne('Post', ['id'=>1]);
    $post->delete();
});
```
해당 Row를 삭제합니다.

### Join
엔티티(테이블로우)끼리 조인과 유사하게 연결관계를 만들고 싶을 때가 있습니다. 클래스에서 `setJoin()`을 통해 연결 엔티티를 생성할 수 있습니다.
```php
<?php
namespace Entities;
class Post extends AbstractEntity
{
    protected function setJoin()
    {
        $this->joinOne('category', 'id', 'parent_id');
        $this->joinMany('comment', 'parent_id');
    }
}
```
```php
$router->add('/', function () {
    $post = $this->container->entityFactory->findOne('Post', ['id'=>1]);
    $category = $post->category; //object
    $comments = $post->comment;  //array
});
```
`joinOne()`은 findOne으로 다른 엔티티를 가져옵니다. `joinMany()`는 find로 가져옵니다.
2번째인자는 가져올 테이블 컬럼이고 3번째인자는 현재 클래스가 가르키는 테이블의 컬럼입니다. 비었을 경우 기본값인 `$this->primaryKey`(id)로 설정됩니다. primaryKey는 오버라이드하여 변경할 수 있습니다.
```php
<?php
namespace Entities;
class Post extends AbstractEntity
{
    protected $primaryKey = 'no';     //primaryKey을 no 컬럼으로 변경
}
```
사실 그냥 컨테이너에 정의된 PDO를 꺼내써도 무방합니다. (더 좋습니다.)




Not Exists Core
---
CHOMini에 건들일 수 없는 Core파일은 없습니다. 어차피 다 합쳐도 몇 줄 안되거든요.
