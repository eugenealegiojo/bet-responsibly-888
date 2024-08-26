import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import './editor.scss';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	return (
		<>
			<div { ...blockProps }>
				<div className="form-container">
					<div className="row-field">
						<input
							type="text"
							placeholder={ __( 'Full name', 'parlay-api' ) }
						/>
						<span className="sc_form_field_hover">
							<i className="sc_form_field_icon trx_addons_icon-user-alt"></i>
						</span>
					</div>
					<div className="row-field">
						<input
							type="text"
							placeholder={ __( 'Nickname', 'parlay-api' ) }
						/>
						<span className="sc_form_field_hover">
							<i className="sc_form_field_icon trx_addons_icon-user-alt"></i>
						</span>
					</div>
					<div className="row-field full-width">
						<input
							type="email"
							placeholder={ __( 'Email', 'parlay-api' ) }
						/>
						<span>
							<i className="sc_form_field_icon trx_addons_icon-mail"></i>
						</span>
					</div>
					<div className="row-field full-width">
						<input
							type="text"
							placeholder={ __( 'Password', 'parlay-api' ) }
						/>
						<span>
							<i className="sc_form_field_icon trx_addons_icon-lock"></i>
						</span>
					</div>
					<div className="row-field">
						<input
							type="text"
							placeholder={ __( 'Date of Birth', 'parlay-api' ) }
						/>
						<span>
							<i className="sc_form_field_icon trx_addons_icon-calendar"></i>
						</span>
					</div>
					<div className="row-field">
						<input
							type="text"
							placeholder={ __( 'City', 'parlay-api' ) }
						/>
						<span>
							<i className="fontello icon-location"></i>
						</span>
					</div>
					<div className="row-field full-width">
						<div className="option-field">
							<label>{ __( 'Sex', 'parlay-api' ) }:</label>
							<div className="radio-choices">
								<label>{ __( 'Male', 'parlay-api' ) }</label>{ ' ' }
								<input type="radio" value="" />
							</div>
							<div className="radio-choices">
								<label>{ __( 'Female', 'parlay-api' ) }</label>{ ' ' }
								<input type="radio" value="" />
							</div>
						</div>
					</div>

					<div className="row-field">
						<input
							type="text"
							placeholder={ __(
								'Cellphone number',
								'parlay-api'
							) }
						/>
						<span>
							<i className="fontello icon-mobile"></i>
						</span>
					</div>
					<div className="row-field">
						<input
							type="text"
							placeholder={ __( 'Phone 2', 'parlay-api' ) }
						/>
						<span>
							<i className="icon-phone-call"></i>
						</span>
					</div>

					<div className="row-field full-width">
						<div className="option-field">
							<input type="checkbox" value="" />
							<label>
								{ __(
									'I wish to receive promotional newsletters, news, and offers via email, SMS, and phone.',
									'parlay-api'
								) }
							</label>
						</div>
					</div>

					<div className="row-field full-width">
						<input
							type="submit"
							name=""
							value={ __( 'Open My Account', 'parlay-api' ) }
						/>
					</div>

					<div className="registration-terms full-width">
						<p>
							{ __(
								'At the end of my registration, I certify that I am over 18 years of age and accept the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a>.',
								'parlay-api'
							) }
						</p>
					</div>
				</div>
			</div>
		</>
	);
};

export default Edit;
