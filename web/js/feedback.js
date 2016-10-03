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
   $('#form_save').prop('disabled', true);
   
   $('#form_text').keyup(function() {
      if($('#form_text').val().length > 300) {
          $('#form_save').prop('disabled', false);
      } else {
          $('#form_save').prop('disabled', true);
      }
   });
});