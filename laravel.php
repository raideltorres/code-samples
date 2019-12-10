<?php
// Simple CRUD operation for users and categories in Laravel using elasticsearch.
// The create category will be only available for admins so we are going to use a middleware to validate this.

// First let's create our migration functions to create our tables
// using the Blueprint and Schema classes from Laravel

public function up() {
  Schema::create('users', function (Blueprint $table)
    $table->increments('id');
    $table->string('name');
    $table->string('lastname');
    $table->string('email')->unique();
    $table->boolean('isAdmin')->nullable();
    $table->timestamps();
  });
}

public function up() {
  Schema::create('categories', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('user_id')->unsigned();
    $table->string('title');
    $table->timestamps();
  });
}

// And we need to define the relation between users and categories
// one to many in this case on each model and activate
// elasticsearch. We also define the fillable fields.
namespace App;
use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\Model;

class User extends Model {

  use ElasticquentTrait;

  protected $fillable = ['name', 'lastname', 'email'];
  protected $mappingProperties = array(
    'name' => [
      'type' => 'text',
      'analyzer' => 'standard'
    ],
    'lastname' => [
      'type' => 'text',
      'analyzer' => 'standard'
    ],
    'email' => [
      'type' => 'text',
      'analyzer' => 'standard'
    ]
  );

  public function categories() {
    return $this->hasMany(Category::class);
  }
}

namespace App;

use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\Model;

class Category extends Model {

  use ElasticquentTrait;

  protected $fillable = ['title', 'user_id'];
  protected $mappingProperties = array(
    'title' => [
      'type' => 'text',
      'analyzer' => 'standard'
    ],
    'user_id' => [
      'type' => 'text',
      'analyzer' => 'standard'
    ]
  );

  public function user() {
    return $this->belongsTo(User::class);
  }
}

// Let's create our middleware for checking if the user
// is an admin
public function handle($request, Closure $next) {
  if(auth()->user()->isAdmin == 1) {
    return $next($request);
  }

  return redirect('category')->with('error','You have not admin access');
}

// We need to define this middleware in the kernel routes
protected $routeMiddleware = ['admin' => \App\Http\Middleware\Admin::class];

// Now let's create a view to get all the required data
// from the user using bootstrap library
// (These views are going to be our Create views)
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Create User View</title>
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="row">
        <div class="col-sm-12">User Data</div>
      </div>
      <form method="post" action="{{url('users')}}" enctype="multipart/form-data">
        @csrf

        <div class="row">
          <div class="form-group col-sm-12">
            <label for="Name">Name:</label>
            <input type="text" class="form-control" name="name">
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-12">
            <label for="LastName">Last Name:</label>
            <input type="text" class="form-control" name="lastName">
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-12">
            <label for="Email">Email:</label>
            <input type="email" class="form-control" name="email">
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-12">
            <button type="submit" class="btn btn-success">Submit</button>
          </div>
        </div>
      </form>
    </div>
  </body>
</html>

// This one is already related to the current user so that is why we are only asking for the title
<!DOCTYPE html>
  <html>
    <head>
      <meta charset="utf-8">
      <title>Create category view</title>
      <link rel="stylesheet" href="{{asset('css/app.css')}}">
      <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    </head>
    <body>
      <div class="container">
        <div class="row">
          <div class="col-sm-12">Category Data</div>
        </div>
      <form method="post" action="{{url('category')}}" enctype="multipart/form-data">
        @csrf

        <div class="row">
          <div class="form-group col-sm-12">
            <label for="Name">Title:</label>
            <input type="text" class="form-control" name="title" />
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-12">
            <button type="submit" class="btn btn-success">Submit</button>
          </div>
        </div>
      </form>
    </div>
  </body>
</html>

// Now we need the routes
Route::resource('users','UsersController');
Route::resource('categories', 'CategoryController')->middleware('admin');

// And the controllers to handle
// both create functionality functionality
// (we are adding a middleware to the categories routes)
namespace App\Http\Controllers\App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class UserController extends Controller {
  public function create() {
    return view('create');
  }

  public function store(Request $request) {
    $user = new User;
    $user->name = $request->get('name');
    $user->lastname = $request->get('lastName');
    $user->email = $request->get('email');
    $user->save();

    return redirect('users')->with('success', 'User created');
  }

  // And we will also create the controller to get the
  // elastic search data from admin users
  public function users() {
    User::createIndex($shards = null, $replicas = null);
    User::putMapping($ignoreConflicts = true);
    User::addAllToIndex();

    $admins = User::searchByQuery(array('match' => array('isAdmin' => true)));

    return view('users', $admins);
  }
}

class CategoryController extends Controller {
  public function store(Request $request) {
    $category = new Category;
    $category->title = $request->get('title');
    $category->user()->associate($request->user());
    $category->save();

    return redirect('categories')->with('success', 'Category created');
  }
}
