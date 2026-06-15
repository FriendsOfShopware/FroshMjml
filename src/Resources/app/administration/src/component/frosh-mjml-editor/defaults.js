export const DEFAULT_TEMPLATE = `<mjml>
    <mj-body>
        <mj-section>
            <mj-column>
                {% block content %}
                {% endblock %}
            </mj-column>
        </mj-section>
    </mj-body>
</mjml>`;

export const DEFAULT_FRAGMENT = `<mj-text font-size="18px" font-weight="bold">{{ headline }}</mj-text>
<mj-text>{{ content }}</mj-text>`;

export function extendsLayoutTemplate(technicalName) {
    return `{% extends mjml_base('${technicalName}') %}
{% block content %}

{% endblock %}`;
}
