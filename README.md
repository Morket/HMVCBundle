Morket's HMVCBundle (NOT STABLE YET)
====================================
[![Build Status](https://travis-ci.org/Morket/HMVCBundle.png)](http://travis-ci.org/Morket/HMVCBundle)

Why are external application calls using a RESTful interface while internal application calls consist of many custom Model methods? Why are internal services different from external services? They shouldn't. The current web is a cloud with services, now you're application can be as well.

The HMVCBundle is a Symfony2 Bundle providing a fast Hierarchical Model View Controller (HMVC) solution utilizing Symfony's dispatch flow to call internal services in the same way you would call it externally. It integrates fully with RESTful controllers from FOSRestBundle, KnpRadBundle and native Symfony.

Concept
-----------
A lot has been written about the Model View Controller pattern and the fact that all your business logic should be put into the Model layer. Conceptually this is totally true, but practically it can be a pain in the ass. A lot of development time goes to mapping Controller actions to Model objects and methods. A lot of time is spent on deciding where to put code and how to structure it well. Where do I put my Forms? Shouldn't I validate from the Model layer as well? If I use annotations to validate my GET and POST values, shouldn't I validate the entity again in the Model layer? Why does my Controller have create/read/update/delete methods mapping to create/read/update/delete methods from my Model layer?

Our statement is that Model View Controller is not that suitable for modern web development and it's outdated. Problem #1 is the fact that there are no real events fired from the View layer, which makes every request just a single application flow. Problem #2 is that we prefer convention over configuration these days, making our Controller layer extremely predictable and repetitive.

We aim for a Presentation Abstraction Control (PAC) approach of web development. Look at a Controller as an entrance to one service within your application. See it as a Control element handling flow, rather than application logic. Your Control layer can call a Form or Doctrine Repository directly, it can perform validation and it can have some business logic. When it gets complex, naturally you will write Abstraction classes to solve this complexity. PAC encourages this. Your Presentation layer will be used for template rendering or serialization (to JSON/XML for example). A template within the Presentation layer could or could not call another Control element to present some extra stuff, like a logged in user on top of a page.

Each service is a MVC/PAC island. You could have a User service and a Product service, which always call each other via the Control element of the service. And because the Control layer is implemented as Symfony2 Controllers, they are also callable from the outside. Using RESTful Bundles like FOSRestBundle or KnpRadBundle, you automatically create an internal and external RESTful API to all your services.

How HMVCBundle works
----------------------------------
The HMVCBundle uses Symfony's normal Request handling flow to make internal calls. It returns the returned data from the Controller
and makes it available elsewhere. It's quite simular to Symfony's forward method, but without the whole HTML/JSON/XML rendering process.
Currently using the HMVC trait adds one method to controllers:
``` php
$this->call($route, $attributes = array(), $data = array(), $query = array(), $rawResponse = false)
```
Param            | Explanation
-----------------|------------------------------------------------------------------------------------------------
**$route**       | is the Symfony route
**$attributes**  | are request/route attributes, which are defined as parameters in your Controller action methods
**$data**        | is POST data
**$query**       | is an array of query params ("GET params")
**$rawResponse** | will force HMVC to return a Symfony2 Response object instead of returning the data directly

You can still use your own Controllers, Event Listeners, Views, Templates and Serializers.
The HMVCBundle won't affect normal behavior, it will only add functionality for internal requests.

**kernel.view event**

The Bundle WILL probably and SHOULD block your kernel.view (onKernelView()) events when making an internal call. This is necessary to make sure no HTML/JSON/XML is rendered for internal (HMVC) requests. The kernel.view event is defined with priority 128, so it will block FOSRestBundle's and KnpRadBundle's kernel.view events, but you can still out-prioritize it. This is native Symfony behavior, Symfony will always allow only one kernel.view per (sub)request, because output can only be rendered once.
(Note: kernel.view is fired when a Controller returns something else than a Response object, like an array of data or a View object)

**Exceptions**

When doing an internal HMVC call, any thrown Exceptions will be thrown through, meaning you can catch them in the Controller where you make the call. See examples below.

How to use it
------------------
Just use the trait Morket\Bundle\HMVCBundle\Controller\HMVC in each Controller you want to make calls from. That's all. HMVC won't affect existing software in any way. You can partially use HMVC, or include the Bundle and not use it at all. It won't break stuff.

**Simple example without dependencies**
``` php
<?php
namespace Acme\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Morket\Bundle\HMVCBundle\Controller\HMVC;

class ProductsController extends Controller
{
    use HMVC;

    public function getProductsAction()
    {
        try {
            $user = $this->call('get_user', ['id' => $this->getRequest()->get('user_id')]);
            // do something
        } catch (NotFoundHttpException $e) {
            // user not found
        }
    }
}
```

**Really practical example using the provided RadRestController, depending on both FOSRestBundle and KnpRadBundle**
``` php
<?php

namespace Acme\Controller;

use Acme\Entity\User;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use Morket\Bundle\HMVCBundle\Controller\RadRestController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UsersController extends RadRestController
{
    /**
     * @View
     */
    public function getUserAction($id)
    {
        return array('user' => $this->findOr404('App:User', ['id' => $id]));
    }

    /**
     * @QueryParam(name="filter", requirements="[a-z]+", strict=true)
     * @View
     */
    public function getUsersAction(ParamFetcher $paramFetcher)
    {
        return ['users' => $this->getRepository('App:User')
                                ->search($paramFetcher->get('filter'))];
    }

    /**
     * @RequestParam(name="username", requirements="[a-z0-9]+")
     * @RequestParam(name="something", requirements="[a-z]+")
     */
    public function postUsersAction($username, $something)
    {
        // We're illustrating the use of @RequestParam here, not best practices in saving a user entity

        $user = new User;
        $user->setUsername($username);
        $user->setSomething($something);
        $this->persist($user, true);

        return $this->redirectView($this->generateUrl('get_user', ['id' => $user->getId()]), 201);
    }

    /**
     * @View
     */
    public function putUserAction($id)
    {
        $user = new User;
        $user->setUsername($this->getRequest()->get('username'));
        $user->setSomething($this->getRequest()->get('something'));
        $this->persist($user, true);

        return $this->redirectView($this->generateUrl('get_user', ['id' => $user->getId()]), 200);
    }

    /**
     * @View
     */
    public function deleteUserAction($id)
    {
        throw new HttpException(500);
    }
}
```
**Somewhere else in your application:**
``` php
<?php
namespace Acme\Controller;

use Morket\Bundle\HMVCBundle\Controller\HMVC;
class InsaneController
{
    use HMVC;

    public function insaneAction()
    {
        $user = $this->call('get_user', ['id' => 1]); // get a user
        $users = $this->call('get_users', [], [], ['filter' => 'mor']); // get users by filter

        $this->call('post_users', ['username' => 'morket', 'something' => 'abc']); // add a user
        $this->call('put_user', ['id' => 1], ['username' => 'morket', 'something' => 'cba']); // update user
        $this->call('delete_user', ['id' => 1]); // delete user
    }
}

```

As you can see, all cool stuff from FOSRestBundle and KnpRadBundle is usable, even the annotation like param specifications.
If you look at the UsersController above, the application will be internally and externally callable like this:

Internal call  | External call
------------------------------------------------------------------------------------- | ----------------------------------------------
$this->call('get_user', ['id' => 1]);                                                 | GET /users/1
$this->call('get_users', [], [], ['filter' => 'mor']);                                | GET /users?filter=mor
$this->call('post_users', ['username' => 'morket', 'something' => 'abc']);            | POST /users with username=morket&something=abc
$this->call('put_user', ['id' => 1], ['username' => 'morket', 'something' => 'cba']); | PUT /users/1 with username=morket&something=cba
$this->call('delete_user', ['id' => 1]);                                              | DELETE /users/1

Do you spot the difference between post_users and put_user looking at the way of providing the data in the internal PHP call?
When using methods parameters as in postUsersAction($username, $something), you should consider these request attributes instead of POST data.
If you are using a normal approach, calling either $request->get('something') or $paramFetcher->get('something'), you should
consider the data POST data.

Magical convertions
-------------------
The HMVCBundle provides a custom Response object extending Symfony's normal HTTP Response object.
When using the call method, HMVCBundle will automatically get the data from the Response object and return only
the data directly to you. If the Controller response is an array or FOSRestBundle View with only one element of data, it will
return only that element. You can see that in action in the examples above.


The HMVC Response object is NOT used when returning a Response object from the controller. It will only be used when
returning an array or View object from the Controller. So if you manually return Response objects everywhere (which is also done
when returning $this->render()), the HMVC component won't be useful, because you'll be getting HTML/JSON/XML in your code.

Todo's
----------------
1. Testing
2. PHP 5.3 support
3. Add HMVC service to be injected by container
4. Convert redirects to data
5. More/better examples in README
6. Automatic caching mechanism
7. Contribute to FOSRestBundle/KnpRadBundle to be able to use both Controllers without copying code

MIT License
-------------------------------------------------------------------------------
Copyright (c) 2013 Morket <http://github.com/morket>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.