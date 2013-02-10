Morket's HMVCBundle
==================

Why are external application calls using a RESTful interface while internal application calls consist of many custom Model methods? Why are internal services different from external services? They shouldn't. The current web is a cloud with services, now you're application can be as well. 

Symfony2 Bundle providing a fast Hierarchical Model View Controller (HMVC) solution utilizing Symfony's dispatch flow to call internal services in the same way you would call it externally. It integrates fully with RESTful controllers from FOSRestBundle, KnpRadBundle and native Symfony. 

Concept
-----------
A lot has been written about the Model View Controller pattern and the fact that all your business logic should be put into the Model layer. Conceptually this is totally true, but practically it can be a pain in the ass. A lot of development time goes to mapping Controller actions to Model objects and methods. A lot of time is spent on deciding where to put code and how to structure it well. Where do I put my Forms? Shouldn't I validate from the Model layer as well? If I use annotations to validate my GET and POST values, shouldn't I validate the entity again in the Model layer? Why does my Controller have create/read/update/delete methods mapping to create/read/update/delete methods from my Model layer?

Our statement is that Model View Controller is not that suitable for modern web development and it's outdated. Problem #1 is the fact that there are no real events fired from the View layer, which makes every request just a single application flow. Problem #2 is that we prefer convention over configuration these days, making our Controller layer extremely predictable and repetitive. 

We aim for a Presentation Abstraction Control (PAC) approach of web development. Look at a Controller as an entrance to one service within your application. See it as a Control element handling flow, rather than application logic. Your Control layer can call a Form or Doctrine Repository directly, it can perform validation and it can have some business logic. When it gets complex, naturally you will write Abstraction classes to solve this complexity. PAC encourages this. Your Presentation layer will be used for template rendering or serialization (to JSON/XML for example). A template within the Presentation layer could or could not call another Control element to present some extra stuff, like a logged in user on top of a page.

Each service is a MVC/PAC island. You could have a User service and a Product service, which always call each other via the Control element of the service. And because the Control layer is implemented as Symfony2 Controllers, they are also callable from the outside. Using RESTful Bundles like FOSRestBundle or KnpRadBundle, you automatically create an internal and external RESTful API to all your services.

How HMVCBundle works
----------------------------------
The HMVCBundle uses Symfony's normal Request handling flow. You can still use your own Controllers, Event Listeners, Views, Templates and Serializers. 

However, it WILL probably and SHOULD block your kernel.view (onKernelView()) events when making an internal call. This is necessary to make sure no HTML/JSON/XML is rendered for internal (HMVC) requests. The kernel.view event is defined with priority 128, so it will block FOSRestBundle's and KnpRadBundle's kernel.view events, but you can still out-prioritize it. This is native Symfony behavior, Symfony will always allow only one kernel.view per (sub)request, because output can only be rendered once. 
(Note: kernel.view is fired when a Controller returns something else than a Response object, like an array of data or a View object)

How to use it
------------------
Just use the trait Morket\Bundle\HMVCBundle\Controller\HMVC in each Controller you want to make calls from. That's all. HMVC won't affect existing software in any way. You can partially use HMVC, or include the Bundle and not use it at all. It won't break stuff. 

``` php
<?php
namespace 'Acme/Controller';
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller 
{

}
``` php

