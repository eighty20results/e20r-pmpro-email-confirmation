/*
 *  Copyright (c) 2019. - Eighty / 20 Results by Wicked Strong Chicks.
 *  ALL RIGHTS RESERVED
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  You can contact us at mailto:info@eighty20results.com
 */

(function ($) {
	'use strict';
	let e20rConfirmationForm = {
		init: function () {
			this.confirmationForm = $('#e20r-email-confirmation-form');
			this.submitBtn = this.confirmationForm.find('input.e20r-email-submit');
			this.toggleSMS = this.confirmationForm.find('input.e20r-use-sms');
			this.smsFields = this.confirmationForm.find('div.e20r-sms-input');
			this.emailFields = this.confirmationForm.find('div.e20r-email-input');

			let self = this;

			self.toggleSMS.unbind('click').on('click', function () {
				window.console.log("Toggle fields");

				if (self.smsFields.is(':hidden')) {
					self.smsFields.show();
					self.emailFields.hide();
				} else {
					self.smsFields.hide();
					self.emailFields.show();
				}
			});

			self.submitBtn.unbind('click').on('click', function (e) {
				e.preventDefault();
				// $('.e20r-warning-message').hide();
				self.submit();
			});
		},
		submit: function () {

			let self = this;
			window.console.log("Trigger submit action!");

			$('html,body').css('cursor', 'progress');

			let request_data = {
				action: 'e20r_send_confirmation',
				'e20r_email_conf': self.confirmationForm.find('input#e20r_email_conf').val(),
				'e20r_email_address': self.confirmationForm.find('input.e20r-recipient-email').val(),
				'e20r_phone_number': self.confirmationForm.find('input.e20r-recipient-phone').val(),
			};

			$.ajax({
				url: e20r_pec.ajaxUrl,
				timeout: (parseInt(e20r_pec.timeout) * 1000),
				data: request_data,
				success: function ($response) {

					let msg = $('.e20r-warning-message');
					let warnings = $('div.e20r-warnings');
					if (false === $response.success) {

						msg.html($response.data);
						msg.addClass('e20r-error-msg');

						warnings.show();
						return false;
					}

					window.console.log("Returned: ", $response);
					let redirect = $('#e20r-redirect-slug').val();
					let $success_msg = $('#e20r-confirmation-msg').val();

					if (typeof redirect !== 'undefined') {
						location.href = '/' + redirect + '/';
					} else {
						msg.addClass('e20r-success-msg');
						msg.html($success_msg);
						warnings.show();
					}

					$('html,body').css('cursor', 'default');
				},
				error: function ($response) {
					window.console.log("Error: ", $response);

					let msg = $('.e20r-warning-message');
					let warnings = $('div.e20r-warnings');

					msg.html($response.data);
					msg.addClass('e20r-error-msg');

					warnings.show();
					$('html,body').css('cursor', 'default');
				},
			});
		}
	}

	$(document).ready(function () {
		e20rConfirmationForm.init();
	});
})(jQuery);
