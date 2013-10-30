package com.controleacesso.socket.swing;

import javax.swing.*;
import com.controleacesso.socket.*;

import java.awt.*;

/**
 *
 * @author Edicarlos Barbosa
 */
public class PortDialog extends JDialog {    
	private static final long serialVersionUID = 1L;
	public static final int UDP = 1;
    public static final int TCP = 2;
    private PortModel model;
    
    /** Creates a new instance of PortDialog */
    public PortDialog(JFrame parent, int type) {
        super(parent);
        if(type==TCP) {
            setTitle("Standard TCP Port");
            model = new PortModel("tcpports.txt");
        } else {
            setTitle("Select UDP port");
            model = new PortModel("udpports.txt");
        }
        Container cp = getContentPane();
        
        JTable table = new JTable(model);
        cp.add(new JScrollPane(table));
        setSize(300,200);
        Util.centerWindow(this);
    }
    
    public String getPort() {
        return model.getPort();
    }
    
}
