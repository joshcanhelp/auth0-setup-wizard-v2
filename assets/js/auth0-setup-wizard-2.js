/* global console, Cookies */

document.addEventListener('DOMContentLoaded', function() {
  'use strict';

  console.log( 'READY' );

  ['wp-auth0-suw-api-token', 'wp-auth0-suw-domain'].forEach(function (id) {
    var field = document.getElementById(id);
    if ( field.value ) {
      Cookies.set( id, field.value );
    } else if ( Cookies.get(id) ) {
      document.getElementById(id).value = Cookies.get(id);
    }
  });
}, false);

document.getElementById('wp-auth0-suw-save-token').addEventListener('click', function () {
  'use strict';

  var messages = [];
  var msgTarget = document.getElementById( 'wp-auth0-suw-token-checks' );
  msgTarget.innerHTML = '';

  var apiTokenId = 'wp-auth0-suw-api-token';
  var apiTokenField = set_field(apiTokenId, messages);
  var domainId = 'wp-auth0-suw-domain';
  var domainField = set_field(domainId, messages);

  if (messages.length) {
    output_messages(msgTarget, messages);
    return;
  }

  Cookies.set( apiTokenId, apiTokenField.value );
  Cookies.set( domainId, domainField.value );

  document.getElementById('wp-auth0-suw-action').value = 'check_token';
  document.getElementById('wp-auth0-suw').submit();
});

function set_field (id, messages) {
  'use strict';

  var field = document.getElementById(id);
  var label = document.querySelectorAll('[for="' + id + '"]')[0];

  field.classList.remove( 'error' );
  if (!field.value) {
    field.classList.add( 'error' );
    messages.push( { type: 'error', msg: label.textContent + ' field cannot be empty.' } );
  }

  return field;
}

function output_messages(container, messages) {
  'use strict';

  if (!messages.length) {
    return;
  }

  messages.forEach(function (el) {
    var message = document.createElement('div');
    message.classList.add(el.type);
    message.classList.add('settings-error');
    message.classList.add('notice');
    message.innerHTML = '<p>' + el.msg + '</p>';
    container.appendChild( message );
  });
}
