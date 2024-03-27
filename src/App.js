const useEffect = wp.element.useState;
const useState  = wp.element.useState;

const App = () => {

	const [option1, setOption1] = useState( '' );
	const [option2, setOption2] = useState( '' );

	useEffect(
		() => {
			/**
															 * Initialize the options fields with the data received from the REST API
															 * endpoint provided by the plugin.
															 */
			wp.apiFetch( {path: '/react-settings-page/v1/options'} ).
			then(
				data => {
					let options = {};
					//Set the new values of the options in the state
					setOption1( data['plugin_option_1'] )
					setOption2( data['plugin_option_2'] )

				},
			);
		}
	);

	return (
		<div>

		<h1>React Settings Page</h1>

		<div>
			<label>Options 1</label>
			<input
				value    ={option1}
				onChange ={(event) => {
					setOption1( event.target.value );
					}}
			/>
		</div>

		<div>
			<label>Options 2</label>
			<input
				value    ={option2}
				onChange ={(event) => {
					setOption2( event.target.value );
					}}
			/>
		</div>

		<button onClick ={() => {

			wp.apiFetch(
				{
					path: '/react-settings-page/v1/options',
					method: 'POST',
					data: {
						'plugin_option_1': option1,
						'plugin_option_2': option2,
					},
				}
			).then(
				data => {
					alert( 'Options saved successfully!' );
				}
			);

			}}>
			{__( 'Save settings', 'react-settings-page' )}
		</button>

		</div>

	);

};
export default App;
