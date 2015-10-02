;(function(global, $, undefined) {
    'use strict';
    function gen_user_name(){
        return Math.random().toString(36).slice(-8);
    }

    function load_thread(json){
        if(json.status == "OK"){
            app.posts = [].concat(json.result);
        }    
    }

    function highlight(){
        var last_id = app.posts.length - 1;
        var selecter = '#posts-' + last_id + '-root';
        // console.log(selecter);
        var offset = $(selecter).offset();
        // console.log(offset);
        window.scrollTo(offset.top, offset.left);
        $(selecter).addClass('bg-highlight');
        window.setTimeout(function(){$(selecter).removeClass('bg-highlight')}, 3000);
    }

    var app = new Vue({
        el: '#app',
        data: {
            posts: [],
            post: {
                content: '',
                screen_name: '',
                user_name: gen_user_name(),
                trust: false,
            }
        },
        methods: {
            run: function(item){
                var prefix = 'posts-' + item.$index;
                var el = document.getElementById(prefix + '-innerHTML');
                el.innerHTML = item.content;
                var el = document.getElementById(prefix + '-testContent');
                el.textContent = item.content;                
            },
            submit: function(){
                var data = 'screen_name=' + app.post.screen_name +
                    '&user_name=' + app.post.user_name + 
                    '&content=' + app.post.content;
                if(app.post.trust){
                    data += '&trust';
                }
                $.ajax({
                    url: "./api/",
                    type: "GET",
                    data: "post_contribution&thread=test&" + data,
                    success: function(json) {
                        // console.log(json);
                        load_thread(json);
                        app.$nextTick(highlight);
                    },
                    error: function(res) {
                        // console.log("data = " + data);
                        console.error(res);
                    }
                });
            },
        }
    });

    window.onload = function(){
        console.log(location);
        var data = "load_thread&thread=test";
        if(location.search){
            data = location.search.slice(1);
        }
        $.ajax({
            url: "./api/",
            type: "GET",
            data: data,
            success: function(json) {
                // console.log(json);
                load_thread(json);
            },
            error: function(res) {
                console.error(res);
            }
        });
    };

})(this, jQuery);