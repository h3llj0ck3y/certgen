<?php

class Certgen_PdfEdit
{
    public function __constructor()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';

    }

    public function replace_tpl_vars()
    {
        // if (is_product() && current_user_can('manage_options')) {

        $customDocTitle = $_POST['cert_title'];
        $product_id = intval($_REQUEST['product_id']);

        $product = wc_get_product($product_id);

        $pdfFile = wp_upload_dir()['basedir'] . '/Expertise_RAW.pdf';
        $date = date('Y-m-d');

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([

            'fontdata' => $fontData + [
                'butler' => [
                    'R' => 'Butler_Regular.ttf',

                ],
                'butlerblack' => [
                    'R' => 'Butler_Black.ttf',

                ],
                'josefinsans' => [
                    'R' => 'JosefinSans-Regular.ttf',
                ],
                'josefinsanslight' => [
                    'R' => 'JosefinSans-Light.ttf',
                ],
            ],
        ]);
        $count = $mpdf->setSourceFile($pdfFile);

        $id = $mpdf->importPage(1);
        $mpdf->useTemplate($id);
        $mpdf->WriteHTML('<div style="position:absolute;text-align:center;width:100%;top:100mm;left:0;">');
        $mpdf->WriteHTML($product->get_image());
        $mpdf->WriteHTML('</div>');
        $mpdf->WriteHTML('<div style="position:absolute;text-align:center;width:100%;top:207.75mm;left:0;font-family:butlerblack;font-size:18pt;">ca. <span style="color:#a69d6e;">' . wc_price(get_post_meta($product->get_id(), 'neupreis_price')[0]) . '</span></div>');
        $mpdf->WriteHTML('<div style="position:absolute;text-align:center;width:100%;top:230mm;font-size:18pt;left:0;font-family:butlerblack;text-transform:uppercase">' . (!$customDocTitle ? $product->get_name() : $customDocTitle) . '</div>');
        $mpdf->WriteHTML('<div style="position:absolute;top:240mm;left:24mm;font-family:josefinsans;font-size:12pt;">');
        $mpdf->WriteHTML($this->wc_display_product_attributes_html($product));

        $mpdf->WriteHTML('</div>');
        $pdfOutputName = $date . '-' . $product->get_sku() . '-' . $customDocTitle . '.pdf';

        // unlink file (delete) if exists
        if (file_exists($pdfOutputName)) {
            unlink($pdfFile);
        }

        $mpdf->Output(wp_upload_dir()['basedir'] . '/' . $pdfOutputName, 'F');
        echo wp_upload_dir()['baseurl'] . '/' . $pdfOutputName;
        exit;
        // }
    }

    public function createGeneratePdfButton()
    {
        $html = '<input type="hidden" name="action" value="generate_pdf">';
        $html = '<div id="major-publishing-actions" style="overflow:hidden">';
        $html .= '<div id="publishing-action">';
        $html .= '<input style="width:100%;" type="text" placeholder="Expertisen Titel" id="cert_title" name="cert_title" />';
        $html .= '<a style="width:100%; text-align:center;" href="' . esc_url(admin_url('admin-ajax.php')) . '"  class="generate_pdf button-primary" id="custom" name="publish">Expertise erstellen</a>';
        $html .= '</div>';
        $html .= '</div>';
        echo $html;
    }

    public function wc_display_product_attributes_html($product)
    {
        $product_attributes = array();


        // Add product attributes to list.
        $attributes = array_filter($product->get_attributes(), 'wc_attributes_array_filter_visible');
        foreach ($attributes as $attribute) {
            $values = array();
            
            if ($attribute->is_taxonomy()) {
                $attribute_taxonomy = $attribute->get_taxonomy_object();
                $attribute_values = wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'all'));

                foreach ($attribute_values as $attribute_value) {
                    $value_name = esc_html($attribute_value->name);
                    
                    if ($attribute_taxonomy->attribute_public) {
                        $values[] = '<a href="' . esc_url(get_term_link($attribute_value->term_id, $attribute->get_name())) . '" rel="tag">' . $value_name . '</a>';
                    } else {
                        $values[] = $value_name;
                    }
                }
            } else {
                $values = $attribute->get_options();
                
                foreach ($values as &$value) {
                    $value = make_clickable(esc_html($value));
                }
            }
            
            $product_attributes['attribute_' . sanitize_title($attribute->get_name())] = array(
                'label' => wc_attribute_label($attribute->get_name()),
                'value' => apply_filters('woocommerce_attribute', wpautop(wptexturize(implode(', ', $values))), $attribute, $values),
            );
            
        }
        // var_dump($product_attributes);
        // wp_die();
        $product_attributes = apply_filters('woocommerce_display_product_attributes_cert', $product_attributes, $product);

        return wc_get_template_html(
            'single-product/product-expertise-attributes.php',
            array(
                'product_attributes' => $product_attributes,
                // Legacy params.
                'product' => $product,
                'attributes' => $attributes,
                'display_dimensions' => $display_dimensions,
            )
        );
    }

    public function certgen_filter($product_attributes, $product)
    {
        $filtered_attributes = array();
        foreach($product_attributes as $key => $attribute) {
            // $filtered_attributes[$key] = array(
            //     'label' => $attribute['label'],
            //     'value' => $attribute['value'],
            // );
            if (get_post_meta($product->get_id(), $key.'_cert',true)) {
                $filtered_attributes[$key] = array(
                    'label' => $attribute['label'],
                    'value' => $attribute['value'],
                );
            }

        }
        return $filtered_attributes;
    }

    public function get_attribute_highlighted($id, $i)
    {
        global $post;
        $id = sanitize_title($id);
        $id = strtolower($id);
        $postid = $post->ID ? $post->ID : $_POST['post_id'];
        $val = get_post_meta($postid, "attribute_" . $id . "_cert", true);
        return !empty($val) ? $val : false;
    }

    public function wcb_add_product_attribute_is_highlighted($attribute, $i = 0)
    {
        $value = boolval($this->get_attribute_highlighted($attribute->get_name(), $i));
        ?>
            <tr>
                <td>
                    <div class="enable_highlighted show_if_canopytour show_if_variable_canopytour">
                        <label>
                        <input type="hidden" class="checkbox" name="attribute_cert[<?php echo esc_attr($i); ?>]" value="0" />
                        <input type="checkbox" class="checkbox"  <?php echo $value ? "checked" : ""; ?> name="attribute_cert[<?php echo esc_attr($i); ?>]" value="1" />
                        In Expertise anzeigen</label>
                    </div>
                </td>
            </tr>
        <?php
}
    public function wcb_ajax_woocommerce_save_attributes()
    {
        check_ajax_referer('save-attributes', 'security');
        parse_str($_POST['data'], $data);
        $post_id = absint($_POST['post_id']);
        if (array_key_exists("attribute_cert", $data)
            && is_array($data["attribute_cert"])) {
            foreach ($data["attribute_cert"] as $i => $val) {
                $attr_name = sanitize_title($data["attribute_names"][$i]);
                $attr_name = strtolower($attr_name);
                update_post_meta($post_id, "attribute_" . $attr_name . "_cert", wc_string_to_bool($val));
            }
        }
    }
}
