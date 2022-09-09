/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Other/javascript.js to edit this template
 */


function toggleActive(e, field)
{
var find = 'textArea' + field;
var tbox = document.getElementById(find);
if (e.checked)
	tbox.removeAttribute('disabled');
else
	tbox.setAttribute('disabled', true);
}
