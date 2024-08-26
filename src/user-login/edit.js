import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	return (
		<>
			<div { ...blockProps }>
				<div className="parlay-login-block">
					<h2>{ __( 'Login', 'parlay-api' ) }</h2>
					<form action="#">
						<input
							type="text"
							placeholder={ __( 'Username', 'parlay-api' ) }
						/>
						<input
							type="password"
							placeholder={ __( 'Password', 'parlay-api' ) }
						/>
						<button type="button">
							{ __( 'Login', 'parlay-api' ) }
						</button>
					</form>
				</div>
			</div>
		</>
	);
};

export default Edit;
