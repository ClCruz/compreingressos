package com.compreingressos.knowledge.lazy;

import com.compreingressos.knowledge.bean.ApresentacaoFacade;
import com.compreingressos.knowledge.model.Apresentacao;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.ejb.EJB;
import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import org.primefaces.model.LazyDataModel;
import org.primefaces.model.SortMeta;

/**
 *
 * @author edicarlos.barbosa
 */
public class ApresentacaoLazyList extends LazyDataModel<Apresentacao>{
    
    @EJB
    private static com.compreingressos.knowledge.bean.ApresentacaoFacade ejbFacade;
    private List<Apresentacao> items = null;   
    
    public ApresentacaoFacade getFacade(){
        Context ctx;  
        try {
            ctx = new InitialContext();
            ejbFacade = (ApresentacaoFacade) ctx.lookup("java:global/Knowledge-1.0.0/ApresentacaoFacade!com.compreingressos.knowledge.bean.ApresentacaoFacade");
        } catch (NamingException ex) {
            Logger.getLogger(ApresentacaoLazyList.class.getName()).log(Level.SEVERE, null, ex);
        }        
        return ejbFacade;
    }

    @Override
    public List<Apresentacao> load(int first, int pageSize, List<SortMeta> multiSortMeta, Map<String, Object> filters) {
        int[] range = {first, pageSize};
        items = getFacade().findRange(range);
        
        if(getRowCount() <= 0){
            setRowCount(getFacade().count());
        }

        setPageSize(pageSize);
        return items;
    }

    @Override
    public Object getRowKey(Apresentacao apresentacao) {
        return apresentacao.getId();
    }

    @Override
    public Apresentacao getRowData(String rowKey) {
        Integer id = Integer.valueOf(rowKey); 
        for (Apresentacao apresentacao : items) {
            if(id.equals(apresentacao.getId())){
                return apresentacao;
            }
        } 
        return null;
    }
        
}
