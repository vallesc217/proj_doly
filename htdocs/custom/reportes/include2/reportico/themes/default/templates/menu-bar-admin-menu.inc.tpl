{% if (SHOW_ADMIN_BUTTON) %}
{% if ADMIN_MENU_URL|length>0 %} 
              <li>
                    <a class="reportico-admin-button2" href="{{ ADMIN_MENU_URL|raw }}">{{ T_ADMIN_MENU }}</a>
              </li>
{% endif %}
{% endif %}
