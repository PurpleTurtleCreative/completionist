import { useState } from '@wordpress/element';
import { Card, CardBody, PanelRow } from '@wordpress/components';

export default function AdminSettingsScreen() {
    const [currentScreen, setCurrentScreen] = useState('dashboard');

    const menuItems = [
        { key: 'dashboard', label: 'Dashboard' },
        { key: 'settings', label: 'Settings' },
        { key: 'reports', label: 'Reports' },
    ];

    const renderScreenContent = () => {
        switch (currentScreen) {
            case 'dashboard':
                return <p>Welcome to the Dashboard. Overview of the plugin functionality goes here.</p>;
            case 'settings':
                return <p>Adjust your plugin settings on this screen.</p>;
            case 'reports':
                return <p>View reports and statistics for your plugin here.</p>;
            default:
                return <p>Select an option from the menu.</p>;
        }
    };

    return (
        <div className='ptc-AdminSettingsScreen'>

						<h1>Completionist &ndash; Settings</h1>

						<div style={styles.container}>
							{/* Floating Sidebar */}
							<Card style={styles.sidebar}>
									<CardBody>
											{menuItems.map((item, index) => (
													<div key={item.key}>
															<PanelRow>
																	<button
																			onClick={() => setCurrentScreen(item.key)}
																			style={{
																					...styles.menuItem,
																					...(currentScreen === item.key
																							? styles.activeMenuItem
																							: {}),
																			}}
																	>
																			{item.label}
																	</button>
															</PanelRow>
													</div>
											))}
									</CardBody>
							</Card>

							{/* Main Content Area */}
							<div style={styles.content}>
									<h2>{menuItems.find((item) => item.key === currentScreen)?.label}</h2>
									<div>{renderScreenContent()}</div>
							</div>
						</div>
        </div>
    );
}

// Inline styles
const styles = {
    container: {
        display: 'flex',
        alignItems: 'flex-start',
        padding: '20px',
        gap: '20px',
    },
    sidebar: {
        width: '200px',
        position: 'sticky',
        top: '20px',
        alignSelf: 'flex-start',
        boxShadow: 'none',
        backgroundColor: 'none',
    },
    menuItem: {
        display: 'block',
        width: '100%',
        padding: '10px 0',
        textAlign: 'left',
        background: 'none',
        border: 'none',
        cursor: 'pointer',
        color: '#333',
        fontSize: '16px',
    },
    activeMenuItem: {
        fontWeight: 'bold',
        color: 'var(--wp-admin-theme-color)',
    },
    separator: {
        border: 'none',
        borderBottom: '1px solid #ddd',
        margin: '0',
    },
    content: {
        flex: 1,
        maxWidth: '800px',
        backgroundColor: '#fff',
        padding: '20px',
        borderRadius: '8px',
        boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
    },
};
