package com.controleacesso.socket.swing;

import java.awt.*;
import javax.swing.*;
import com.controleacesso.socket.*;

/**
 *
 * @author Edicarlos Barbosa
 */
public class SplashScreen extends JWindow {
	private static final long serialVersionUID = 1L;
	protected ImageIcon logo;
    protected JLabel productName;
    
    public SplashScreen() {
	ClassLoader cl = getClass().getClassLoader();
        logo = new ImageIcon(cl.getResource("icons/logo.gif"));
        productName = new JLabel("<html><font face=\"Verdana\" size=10>"+
                "Controle de Acesso - Servidor v 1.0.0",logo,JLabel.CENTER);
        productName.setOpaque(true);
        
        productName.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createEmptyBorder(10,10,10,10),
                BorderFactory.createLineBorder(Color.black) ));
        getContentPane().add(productName);
        Dimension dim=productName.getPreferredSize();
        dim.setSize(dim.getWidth()+10,dim.getHeight()+10);
        setSize(dim);
        Util.centerWindow(this);
        setVisible(true);
    }
    
    public void kill() {
        dispose();
    }
    
}
