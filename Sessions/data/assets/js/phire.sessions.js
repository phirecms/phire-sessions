/**
 * Sessions Module Scripts for Phire CMS 2
 */

jax(document).ready(function(){
    if ((jax.query('failed') != undefined) || (jax.query('expired') != undefined)) {
        jax('body').append('div', {"id" : "session-failure"});
        jax('#session-failure').css('opacity', 0);
        jax('#session-failure').val(((jax.query('failed') != undefined) ? 'Login Failed.' : 'Session Expired.'));
        jax('#session-failure').fade(100, {tween : 10, speed: 200});
        phire.clear = setTimeout(function(){
            phire.clearStatus('#session-failure');
        }, 3000);
    }
    if (jax('#sessions-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#sessions-form').checkAll(this.value);
            } else {
                jax('#sessions-form').uncheckAll(this.value);
            }
        });
        jax('#sessions-form').submit(function(){
            return jax('#sessions-form').checkValidate('checkbox', true);
        });
    }
    if (jax('#users-sessions-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#users-sessions-form').checkAll(this.value);
            } else {
                jax('#users-sessions-form').uncheckAll(this.value);
            }
        });
        jax('#users-sessions-form').submit(function(){
            return jax('#users-sessions-form').checkValidate('checkbox', true);
        });
    }

    var timeout = jax.cookie.load('phire_session_timeout');
    var path    = decodeURIComponent(jax.cookie.load('phire_session_path'));
    if (timeout != '') {
        setInterval(function(){
            if (jax('#session-timeout')[0] == undefined) {
                jax('body').append('div', {"id": "session-timeout"});
                jax('#session-timeout').css('opacity', 0);
                jax('#session-timeout').val(
                    '<h4 id="countdown">30</h4>Your session is about to expire.<br /><span><a href="' + path +
                    '/sessions/json" onclick="jax.get(this.href); phire.clearStatus(\'#session-timeout\'); return false;">Continue</a>?</span>'
                );
                jax('#session-timeout').fade(100, {tween: 10, speed: 200});

                setInterval(function(){
                    var sec = parseInt(jax('#countdown').val());
                    if (sec > 0) {
                        var newSec = sec - 1;
                        jax('#countdown').val(newSec);
                    } else {
                        window.location = path + '/logout';
                    }
                }, 1000);
            }
        }, timeout * 1000);
    }
});