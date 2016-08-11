/* 
 * Copyright (C) 2016 dinel
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


$( document ).ready(function() {
    var no_comparison = 0;
    
    $('.comparison').click(function() {
        if($(this).hasClass('selected')) {
            $(this).html('Add to comparison');
            no_comparison--;
        } else {
            $(this).html('Remove from comparison');
            no_comparison++
        }
        $(this).toggleClass('selected');        
    });        
    
    $('#compare').click(function () {
       if(no_comparison < 2) {
           alert("Please select at least 2 activities");
       } else {
           var selection = "";
           $('.comparison').filter('.selected').each(function(index) {
               selection += ("-" + $(this).attr("id").substring(1));
           });
           
           window.location.href = "/analysis/compare/" + selection.substring(1);
       }
    });
});