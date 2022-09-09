function checkEmailAddress()
	{
	var lF = document.getElementById('loginField');
	if (!lF)
		{
		alert("Login field not found");
		return false;
		}
	if (lF.value == "" )
		{
		alert("Please enter your email address to reset this account");
		}
	return true;
	}

function setSystemTimezone()
	{
	// set the timezone found in the browser.
	var e = document.getElementById('browserTz');
	if (e)
		{
		tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
		e.innerHTML = tz;
		var f = document.getElementById('systemTz');
		if (f)
			f.value = tz;
		}
	}

function filterTimezoneSelect()
	{
	// first resolve the hour, PM = amhour + 12 EG 14 is 2:00 PM
	var hour = document.getElementById('hour').value;
	var am = document.getElementById('amRadio');
	if (am.checked == false)
		{
		// PM times
		// cast to int
		if (hour < 12)
			hour = (hour - 0) + 12;
		}
	else
		{
		// AM times, this should only happen for 12 AM
		if (hour > 11)
			hour = (hour - 0) - 12;
		}
	var tzClass = "timezone" + hour;
	$(".timezoneSelect").hide();
	$("." + tzClass).show();
	return true;
	}


function incrementHour(a, h)
	{
	var ch = document.getElementById('hour');
	// cast to integer.
	var hour = ch.value - 0;
	var beforehour = hour;
	hour = hour + h;
	if (hour > 12)
		{
		hour = hour - 12;
		}
	else if (hour <1)
		{
		hour = hour + 12;
		}
	if (beforehour == 11 && hour == 12)
		switchAmPm();
	else if (beforehour == 12 && hour == 11)
		switchAmPm();

	// cast to string
	hour = "" + hour;
	if (hour.length == 1)
		hour = "0" + hour;
	ch.value = hour;
	filterTimezoneSelect();
	return true;
	}

function switchAmPm()
	{
	var am = document.getElementById('amRadio');
	var pm = document.getElementById('pmRadio');
	if (am.checked)
		{
		am.checked = false;
		pm.checked = true;
		}
	else
		{
		am.checked = true;
		pm.checked = false;
		}
	}

/**
 *
 * @param {string} hour
 * @returns {undefined}
 */

function togglePwVisible(e)
	{
	var p = document.getElementById('password1');
	p.type = e.checked ? 'text' : 'password';
	var p = document.getElementById('password2');
	p.type = e.checked ? 'text' : 'password';
	}

function checkPasswordRules()
	{
	var psubmit = document.getElementById('savePwButton');
	var p1 = document.getElementById('password1');
	var p2 = document.getElementById('password2');
	if (!p1)
		return;
	psubmit.disabled = true;
	var req1 = document.getElementById('req1');
	var success = true;
	if (p1.value.length < 8)
		{
		req1.classList.remove('loginGreyed');
		success = false;
		}
	else
		req1.classList.add('loginGreyed');

	var str = p1.value;

	var req2 = document.getElementById('req2');
	if (!str.match(/[A-Z]/g))
		{
		req2.classList.remove('loginGreyed');
		success = false;
		}
	else
		req2.classList.add('loginGreyed');


	var req3 = document.getElementById('req3');
  if (!str.match(/[a-z]/g))
		{
		req3.classList.remove('loginGreyed');
		success = false;
		}
	else
		req3.classList.add('loginGreyed');

	var req4 = document.getElementById('req4');
	if (!str.match(/[0-9]/g))
		{
		req4.classList.remove('loginGreyed');
		success = false;
		}
	else
		req4.classList.add('loginGreyed');

	var req5 = document.getElementById('req5');
	if (p1.value != p2.value || (!p1.value) )
		{
		req5.classList.remove('loginGreyed');
		success = false;
		}
	else
		req5.classList.add('loginGreyed');

	if (success)
		psubmit.disabled = false;
	}
