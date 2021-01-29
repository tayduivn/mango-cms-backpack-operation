@extends(backpack_view('blank'))
@php
    $widgets['before_content'][] = [
        'type'        => 'jumbotron',
        'heading'     => trans('backpack::base.welcome'),
        'content'     => trans('backpack::base.use_sidebar'),
        'button_link' => backpack_url('logout'),
        'button_text' => trans('backpack::base.logout'),
    ];
@endphp

@php
    // ---------------------
    // JUMBOTRON widget demo
    // ---------------------
    // Widget::add([
 //        'type'        => 'jumbotron',
 //        'name' 		  => 'jumbotron',
 //        'wrapperClass'=> 'shadow-xs',
 //        'heading'     => trans('backpack::base.welcome'),
 //        'content'     => trans('backpack::base.use_sidebar'),
 //        'button_link' => backpack_url('logout'),
 //        'button_text' => trans('backpack::base.logout'),
 //    ])->to('before_content')->makeFirst();

    // -------------------------
    // FLUENT SYNTAX for widgets
    // -------------------------
    // Using the progress_white widget
    //
    // Obviously, you should NOT do any big queries directly in the view.
    // In fact, it can be argued that you shouldn't add Widgets from blade files when you
    // need them to show information from the DB.
    //
    // But you do whatever you think it's best. Who am I, your mom?
    $productCount = App\Models\Post::count();
    $userCount = App\Models\User::count();
    $categoryCount = App\Models\Category::count();
    $lastCategory = NULL;$lastCategoryDaysAgo="0";
    if((App\Models\Category::all()) == NULL){
         $lastCategory = App\Models\Category::orderBy('created_at', 'DESC')->first();
        $lastCategoryDaysAgo = \Carbon\Carbon::parse($lastCategory->created_at)->diffInDays(\Carbon\Carbon::today());
    }
     // notice we use Widget::add() to add widgets to a certain group
    Widget::add()->to('before_content')->type('div')->class('row')->content([
        // notice we use Widget::make() to add widgets as content (not in a group)
        Widget::make()
            ->type('progress')
            ->class('card border-0 text-white bg-primary')
            ->progressClass('progress-bar')
            ->value($userCount)
            ->description('Registered users.')
            ->progress(100*(int)$userCount/1000)
            ->hint(1000-$userCount.' more until next milestone.'),
        // alternatively, to use widgets as content, we can use the same add() method,
        // but we need to use onlyHere() or remove() at the end
        Widget::add()
            ->type('progress')
            ->class('card border-0 text-white bg-success')
            ->progressClass('progress-bar')
            ->value($categoryCount)
            ->description('Category.')
            ->progress(80)
            ->hint('Great! Don\'t stop.')
            ->onlyHere(),
        // alternatively, you can just push the widget to a "hidden" group
        Widget::make()
            ->group('hidden')
            ->type('progress')
            ->class('card border-0 text-white bg-warning')
            ->value($lastCategoryDaysAgo.' days')
            ->progressClass('progress-bar')
            ->description('Since last Category.')
            ->progress(30)
            ->hint('Post an article every 3-4 days.'),
        // both Widget::make() and Widget::add() accept an array as a parameter
        // if you prefer defining your widgets as arrays
        Widget::make([
            'type' => 'progress',
            'class'=> 'card border-0 text-white bg-dark',
            'progressClass' => 'progress-bar',
            'value' => $productCount,
            'description' => 'Post.',
            'progress' => (int)$productCount/75*100,
            'hint' => $productCount>75?'Try to stay under 75 products.':'Good. Good.',
        ]),
    ]);

    $widgets['after_content'][] = [
      'type' => 'div',
      'class' => 'row',
      'content' => [ // widgets
           [
              'type' => 'card',
              // 'wrapperClass' => 'col-sm-6 col-md-4', // optional
              // 'class' => 'card bg-dark text-white', // optional
              'content' => [
                  'header' => 'Some card title', // optional
                  'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis non mi nec orci euismod venenatis. Integer quis sapien et diam facilisis facilisis ultricies quis justo. Phasellus sem <b>turpis</b>, ornare quis aliquet ut, volutpat et lectus. Aliquam a egestas elit. <i>Nulla posuere</i>, sem et porttitor mollis, massa nibh sagittis nibh, id porttitor nibh turpis sed arcu.',
              ]
            ],
            [
              'type' => 'card',
              // 'wrapperClass' => 'col-sm-6 col-md-4', // optional
              // 'class' => 'card bg-dark text-white', // optional
              'content' => [
                  'header' => 'Another card title', // optional
                  'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis non mi nec orci euismod venenatis. Integer quis sapien et diam facilisis facilisis ultricies quis justo. Phasellus sem <b>turpis</b>, ornare quis aliquet ut, volutpat et lectus. Aliquam a egestas elit. <i>Nulla posuere</i>, sem et porttitor mollis, massa nibh sagittis nibh, id porttitor nibh turpis sed arcu.',
              ]
            ],
            [
              'type' => 'card',
              // 'wrapperClass' => 'col-sm-6 col-md-4', // optional
              // 'class' => 'card bg-dark text-white', // optional
              'content' => [
                  'header' => 'Yet another card title', // optional
                  'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis non mi nec orci euismod venenatis. Integer quis sapien et diam facilisis facilisis ultricies quis justo. Phasellus sem <b>turpis</b>, ornare quis aliquet ut, volutpat et lectus. Aliquam a egestas elit. <i>Nulla posuere</i>, sem et porttitor mollis, massa nibh sagittis nibh, id porttitor nibh turpis sed arcu.',
              ]
            ],
      ]
    ];
    $widgets['after_content'][] = [
      'type'         => 'alert',
      'class'        => 'alert alert-warning bg-dark border-0 mb-4',
      'heading'      => 'Demo Refreshes Every Hour on the Hour',
      'content'      => 'At hh:00, all custom entries are deleted, all files, everything. This cleanup is necessary because developers like to joke with their test entries, and mess with stuff. But you know that :-) Go ahead - make a developer smile.' ,
      'close_button' => true, // show close button or not
    ];
     $widgets['before_content'][] = [
	  'type' => 'div',
	  'class' => 'row',
	  'content' => [ // widgets
		  	[
		        'type' => 'chart',
		        'wrapperClass' => 'col-md-6',
		        // 'class' => 'col-md-6',
		        'controller' => \App\Http\Controllers\Admin\Charts\WeeklyUsersChartController::class,
				'content' => [
				    'header' => 'New Users Past 7 Days', // optional
                 'body' => 'This chart should make it obvious how many new users have signed up in the past 7 days.<br><br>', // optional
		    	]
	    	],
	    	[
	    	    'type' => 'chart',
		        'wrapperClass' => 'col-md-6',
		        // 'class' => 'col-md-6',
		        'controller' => \App\Http\Controllers\Admin\Charts\NewEntriesChartController::class,
				'content' => [
				    'header' => 'New Entries', // optional
                 'body' => 'This chart should make it obvious how many things have changed in the past 30 days.<br><br>', // optional
		    	]
            ],
       ]
     ];
@endphp
@section('content')
    {{-- In case widgets have been added to a 'content' group, show those widgets. --}}
    @include(backpack_view('inc.widgets'), [ 'widgets' => app('widgets')->where('group', 'content')->toArray() ])
@endsection

